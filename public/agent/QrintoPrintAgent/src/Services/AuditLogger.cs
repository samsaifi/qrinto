using System.Text.Json;
using Microsoft.Extensions.Options;
using QrintoPrintAgent.Config;
using QrintoPrintAgent.Models;

namespace QrintoPrintAgent.Services;

/// <summary>
/// Writes structured audit entries to local JSON log files (Section 3.2.6, 7.2).
/// Logs are rotated daily and retained for 90 days.
/// </summary>
public class AuditLogger : IDisposable
{
    private readonly AgentConfig _config;
    private readonly ILogger<AuditLogger> _logger;
    private readonly string _logDirectory;
    private readonly SemaphoreSlim _writeLock = new(1, 1);
    private static readonly JsonSerializerOptions _jsonOptions = new()
    {
        WriteIndented = false,
        PropertyNamingPolicy = JsonNamingPolicy.CamelCase
    };

    public AuditLogger(IOptions<AgentConfig> config, ILogger<AuditLogger> logger)
    {
        _config = config.Value;
        _logger = logger;
        _logDirectory = Path.GetFullPath(_config.Logging.LogDirectory);
        Directory.CreateDirectory(_logDirectory);
    }

    /// <summary>
    /// Writes an audit entry to today's log file.
    /// Each entry is a single JSON line (JSONL format).
    /// </summary>
    public async Task LogAsync(AuditEntry entry)
    {
        entry.Timestamp = DateTime.UtcNow;

        var fileName = $"audit-{DateTime.UtcNow:yyyy-MM-dd}.jsonl";
        var filePath = Path.Combine(_logDirectory, fileName);

        var json = JsonSerializer.Serialize(entry, _jsonOptions);

        await _writeLock.WaitAsync();
        try
        {
            await File.AppendAllTextAsync(filePath, json + Environment.NewLine);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to write audit entry for order {OrderId}", entry.OrderId);
        }
        finally
        {
            _writeLock.Release();
        }
    }

    /// <summary>
    /// Deletes log files older than the configured retention period.
    /// Called on startup and daily thereafter.
    /// </summary>
    public void CleanupOldLogs()
    {
        try
        {
            var cutoff = DateTime.UtcNow.AddDays(-_config.Logging.RetentionDays);

            foreach (var file in Directory.GetFiles(_logDirectory, "audit-*.jsonl"))
            {
                var fileInfo = new FileInfo(file);
                if (fileInfo.LastWriteTimeUtc < cutoff)
                {
                    fileInfo.Delete();
                    _logger.LogInformation("Deleted old audit log: {File}", fileInfo.Name);
                }
            }
        }
        catch (Exception ex)
        {
            _logger.LogWarning(ex, "Failed to clean up old audit logs");
        }
    }

    public void Dispose()
    {
        _writeLock.Dispose();
    }
}
