using Microsoft.Extensions.Options;
using QrintoPrintAgent.Config;
using QrintoPrintAgent.Models;
using QrintoPrintAgent.Services;

namespace QrintoPrintAgent.Core;

/// <summary>
/// Background worker service that orchestrates the Print Agent lifecycle.
/// Connects to backend, processes print jobs, reports health.
/// Runs as a Windows Service via Microsoft.Extensions.Hosting.WindowsServices.
/// </summary>
public class PrintAgentWorker : BackgroundService
{
    private readonly ILogger<PrintAgentWorker> _logger;
    private readonly AgentConfig _config;
    private readonly ConnectionManager _connectionManager;
    private readonly JobReceiver _jobReceiver;
    private readonly TrayResolver _trayResolver;
    private readonly PrintExecutor _printExecutor;
    private readonly StatusReporter _statusReporter;
    private readonly AuditLogger _auditLogger;

    public PrintAgentWorker(
        ILogger<PrintAgentWorker> logger,
        IOptions<AgentConfig> config,
        ConnectionManager connectionManager,
        JobReceiver jobReceiver,
        TrayResolver trayResolver,
        PrintExecutor printExecutor,
        StatusReporter statusReporter,
        AuditLogger auditLogger)
    {
        _logger = logger;
        _config = config.Value;
        _connectionManager = connectionManager;
        _jobReceiver = jobReceiver;
        _trayResolver = trayResolver;
        _printExecutor = printExecutor;
        _statusReporter = statusReporter;
        _auditLogger = auditLogger;
    }

    protected override async Task ExecuteAsync(CancellationToken stoppingToken)
    {
        _logger.LogInformation(
            "Qrinto Print Agent starting. Store: {StoreId}, Printer: {Printer}",
            _config.StoreId, _config.Printer.PrinterName);

        // ── 1. Initialize ──
        _trayResolver.Initialize();
        _auditLogger.CleanupOldLogs();

        await _auditLogger.LogAsync(new AuditEntry
        {
            OrderId = "SYSTEM",
            Action = "agent_started",
            Metadata = new Dictionary<string, string>
            {
                ["storeId"] = _config.StoreId,
                ["printer"] = _config.Printer.PrinterName
            }
        });

        // ── 2. Register WebSocket message handler ──
        _connectionManager.OnMessageReceived += async (message) =>
        {
            await HandleBackendMessageAsync(message, stoppingToken);
        };

        // ── 3. Start concurrent tasks ──
        var connectionTask = _connectionManager.ConnectAsync(stoppingToken);
        var processingTask = ProcessJobLoopAsync(stoppingToken);
        var healthTask = HealthReportLoopAsync(stoppingToken);

        try
        {
            await Task.WhenAll(connectionTask, processingTask, healthTask);
        }
        catch (OperationCanceledException) when (stoppingToken.IsCancellationRequested)
        {
            _logger.LogInformation("Print Agent shutting down gracefully");
        }
        catch (Exception ex)
        {
            _logger.LogCritical(ex, "Print Agent encountered a fatal error");
            throw;
        }

        await _auditLogger.LogAsync(new AuditEntry
        {
            OrderId = "SYSTEM",
            Action = "agent_stopped"
        });
    }

    /// <summary>
    /// Handles incoming messages from the backend (new jobs, config updates).
    /// </summary>
    private async Task HandleBackendMessageAsync(BackendMessage message, CancellationToken cancellationToken)
    {
        switch (message.Type)
        {
            case "print_job":
                if (message.Job != null)
                {
                    await _jobReceiver.HandleJobAsync(message.Job, cancellationToken);
                }
                break;

            case "config_update":
                if (message.TrayConfig != null)
                {
                    _trayResolver.UpdateMappings(message.TrayConfig);
                    await _auditLogger.LogAsync(new AuditEntry
                    {
                        OrderId = "SYSTEM",
                        Action = "config_synced",
                        Metadata = new Dictionary<string, string>
                        {
                            ["trayCount"] = message.TrayConfig.Count.ToString()
                        }
                    });
                }
                break;

            case "pong":
                _logger.LogDebug("Received pong from backend");
                break;

            default:
                _logger.LogWarning("Unknown message type: {Type}", message.Type);
                break;
        }
    }

    /// <summary>
    /// Continuously dequeues and processes print jobs.
    /// </summary>
    private async Task ProcessJobLoopAsync(CancellationToken cancellationToken)
    {
        while (!cancellationToken.IsCancellationRequested)
        {
            var job = _jobReceiver.Dequeue();

            if (job != null)
            {
                _logger.LogInformation("Processing job {JobId} for order {OrderId}",
                    job.JobId, job.OrderId);

                var result = await _printExecutor.ExecuteAsync(job, cancellationToken);
                await _statusReporter.ReportJobResultAsync(job, result);
            }
            else
            {
                // No jobs in queue, wait briefly before checking again
                await Task.Delay(200, cancellationToken);
            }
        }
    }

    /// <summary>
    /// Sends printer health reports every 60 seconds (Section 7.1).
    /// </summary>
    private async Task HealthReportLoopAsync(CancellationToken cancellationToken)
    {
        while (!cancellationToken.IsCancellationRequested)
        {
            await Task.Delay(60_000, cancellationToken);
            await _statusReporter.ReportHealthAsync();
        }
    }
}
