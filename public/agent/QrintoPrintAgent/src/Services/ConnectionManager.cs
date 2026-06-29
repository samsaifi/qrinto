using System.Net.WebSockets;
using System.Text;
using System.Text.Json;
using Microsoft.Extensions.Options;
using QrintoPrintAgent.Config;
using QrintoPrintAgent.Models;

namespace QrintoPrintAgent.Services;

/// <summary>
/// Manages WebSocket lifecycle: connect, heartbeat, auto-reconnect with
/// exponential backoff, and fallback to HTTP polling (Section 2.3, 3.2.1).
/// </summary>
public class ConnectionManager : IDisposable
{
    private readonly AgentConfig _config;
    private readonly ILogger<ConnectionManager> _logger;
    private readonly HttpClient _httpClient;
    private ClientWebSocket? _ws;
    private CancellationTokenSource? _cts;
    private int _reconnectDelayMs = 1000;
    private bool _disposed;

    public bool IsConnected => _ws?.State == WebSocketState.Open;
    public event Func<BackendMessage, Task>? OnMessageReceived;

    public ConnectionManager(IOptions<AgentConfig> config, ILogger<ConnectionManager> logger)
    {
        _config = config.Value;
        _logger = logger;
        _httpClient = new HttpClient();
        _httpClient.DefaultRequestHeaders.Add("Authorization", $"Bearer {_config.ApiKey}");
        _httpClient.DefaultRequestHeaders.Add("X-Store-Id", _config.StoreId);
    }

    /// <summary>
    /// Establishes WebSocket connection to the backend.
    /// On failure, retries with exponential backoff (1s → 2s → 4s → ... → max 60s).
    /// </summary>
    public async Task ConnectAsync(CancellationToken cancellationToken)
    {
        _cts = CancellationTokenSource.CreateLinkedTokenSource(cancellationToken);

        while (!_cts.Token.IsCancellationRequested)
        {
            try
            {
                _ws?.Dispose();
                _ws = new ClientWebSocket();
                _ws.Options.SetRequestHeader("Authorization", $"Bearer {_config.ApiKey}");
                _ws.Options.SetRequestHeader("X-Store-Id", _config.StoreId);

                _logger.LogInformation("Connecting to WebSocket: {Url}", _config.Server.WebSocketUrl);
                await _ws.ConnectAsync(new Uri(_config.Server.WebSocketUrl), _cts.Token);

                _logger.LogInformation("WebSocket connected successfully");
                _reconnectDelayMs = 1000; // Reset backoff on success

                // Start receive loop and heartbeat concurrently
                var receiveTask = ReceiveLoopAsync(_cts.Token);
                var heartbeatTask = HeartbeatLoopAsync(_cts.Token);
                await Task.WhenAny(receiveTask, heartbeatTask);
            }
            catch (WebSocketException ex)
            {
                _logger.LogWarning(ex, "WebSocket connection failed. Retrying in {Delay}ms", _reconnectDelayMs);
            }
            catch (OperationCanceledException) when (_cts.Token.IsCancellationRequested)
            {
                _logger.LogInformation("Connection cancelled");
                return;
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Unexpected connection error");
            }

            // Exponential backoff
            await Task.Delay(_reconnectDelayMs, _cts.Token);
            _reconnectDelayMs = Math.Min(_reconnectDelayMs * 2, _config.Server.ReconnectMaxDelayMs);
        }
    }

    /// <summary>
    /// Sends a message to the backend via WebSocket, or falls back to HTTP POST.
    /// </summary>
    public async Task SendAsync(AgentMessage message, CancellationToken cancellationToken = default)
    {
        var json = JsonSerializer.Serialize(message);
        var bytes = Encoding.UTF8.GetBytes(json);

        if (IsConnected)
        {
            await _ws!.SendAsync(
                new ArraySegment<byte>(bytes),
                WebSocketMessageType.Text,
                endOfMessage: true,
                cancellationToken
            );
        }
        else
        {
            // HTTP fallback
            _logger.LogDebug("WebSocket unavailable, falling back to HTTP POST");
            var content = new StringContent(json, Encoding.UTF8, "application/json");
            await _httpClient.PostAsync($"{_config.Server.BackendUrl}/api/agent/messages", content, cancellationToken);
        }
    }

    /// <summary>
    /// Polls the backend for pending jobs when WebSocket is unavailable (Section 2.3).
    /// </summary>
    public async Task<List<PrintJob>> PollPendingJobsAsync(CancellationToken cancellationToken)
    {
        var response = await _httpClient.GetAsync(
            $"{_config.Server.BackendUrl}/api/print-jobs/pending?storeId={_config.StoreId}",
            cancellationToken
        );
        response.EnsureSuccessStatusCode();

        var json = await response.Content.ReadAsStringAsync(cancellationToken);
        return JsonSerializer.Deserialize<List<PrintJob>>(json) ?? new List<PrintJob>();
    }

    // ── Private helpers ──

    private async Task ReceiveLoopAsync(CancellationToken cancellationToken)
    {
        var buffer = new byte[8192];

        while (!cancellationToken.IsCancellationRequested && IsConnected)
        {
            try
            {
                var result = await _ws!.ReceiveAsync(new ArraySegment<byte>(buffer), cancellationToken);

                if (result.MessageType == WebSocketMessageType.Close)
                {
                    _logger.LogInformation("Server initiated WebSocket close");
                    break;
                }

                var json = Encoding.UTF8.GetString(buffer, 0, result.Count);
                var message = JsonSerializer.Deserialize<BackendMessage>(json);

                if (message != null && OnMessageReceived != null)
                {
                    await OnMessageReceived.Invoke(message);
                }
            }
            catch (WebSocketException ex)
            {
                _logger.LogWarning(ex, "WebSocket receive error");
                break;
            }
        }
    }

    private async Task HeartbeatLoopAsync(CancellationToken cancellationToken)
    {
        while (!cancellationToken.IsCancellationRequested && IsConnected)
        {
            await Task.Delay(_config.Server.HeartbeatIntervalMs, cancellationToken);

            var ping = new AgentMessage { Type = "ping", Timestamp = DateTime.UtcNow };
            await SendAsync(ping, cancellationToken);
        }
    }

    public void Dispose()
    {
        if (_disposed) return;
        _disposed = true;
        _cts?.Cancel();
        _ws?.Dispose();
        _httpClient.Dispose();
    }
}
