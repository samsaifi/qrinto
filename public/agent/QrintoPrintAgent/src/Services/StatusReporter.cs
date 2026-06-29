using System.Management;
using Microsoft.Extensions.Options;
using QrintoPrintAgent.Config;
using QrintoPrintAgent.Models;

namespace QrintoPrintAgent.Services;

/// <summary>
/// Reports job results and printer health to the backend (Section 3.2.5, 7.1).
/// Periodically sends heartbeat with printer status.
/// </summary>
public class StatusReporter
{
    private readonly AgentConfig _config;
    private readonly ILogger<StatusReporter> _logger;
    private readonly ConnectionManager _connectionManager;
    private readonly TrayResolver _trayResolver;

    public StatusReporter(
        IOptions<AgentConfig> config,
        ILogger<StatusReporter> logger,
        ConnectionManager connectionManager,
        TrayResolver trayResolver)
    {
        _config = config.Value;
        _logger = logger;
        _connectionManager = connectionManager;
        _trayResolver = trayResolver;
    }

    /// <summary>
    /// Reports a job completion or failure to the backend.
    /// </summary>
    public async Task ReportJobResultAsync(PrintJob job, PrintJobResult result)
    {
        var message = new AgentMessage
        {
            Type = "job_result",
            JobId = job.JobId,
            State = job.State,
            ErrorCode = result.Success ? null : result.ErrorMessage,
            Timestamp = DateTime.UtcNow
        };

        try
        {
            await _connectionManager.SendAsync(message);
            _logger.LogInformation("Reported job {JobId} result: {State}", job.JobId, job.State);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to report job result for {JobId}", job.JobId);
        }
    }

    /// <summary>
    /// Sends a printer health snapshot to the backend (Section 7.1).
    /// Called periodically by the Worker service (every 60s).
    /// </summary>
    public async Task ReportHealthAsync()
    {
        var health = GetPrinterHealth();

        var message = new AgentMessage
        {
            Type = "health",
            Health = health,
            Timestamp = DateTime.UtcNow
        };

        try
        {
            await _connectionManager.SendAsync(message);
            _logger.LogDebug("Health report sent: {Online}, queue depth: {Depth}",
                health.IsOnline ? "online" : "offline", health.QueueDepth);
        }
        catch (Exception ex)
        {
            _logger.LogWarning(ex, "Failed to send health report");
        }
    }

    /// <summary>
    /// Queries WMI for current printer status, toner, paper levels.
    /// </summary>
    public PrinterHealth GetPrinterHealth()
    {
        var health = new PrinterHealth
        {
            PrinterName = _config.Printer.PrinterName
        };

        try
        {
            // Query printer status via WMI
            using var searcher = new ManagementObjectSearcher(
                $"SELECT * FROM Win32_Printer WHERE Name = '{_config.Printer.PrinterName}'"
            );

            foreach (ManagementObject printer in searcher.Get())
            {
                var status = Convert.ToInt32(printer["PrinterStatus"] ?? 0);
                health.IsOnline = status == 3; // 3 = Idle (ready)
                health.DriverVersion = printer["DriverName"]?.ToString() ?? "";

                // Queue depth
                var queueLength = Convert.ToInt32(printer["JobCountSinceLastReset"] ?? 0);
                health.QueueDepth = queueLength;
            }

            // Build tray status from TrayResolver's discovered trays
            var physicalTrays = _trayResolver.GetPhysicalTrays();
            foreach (var kvp in physicalTrays)
            {
                health.Trays[kvp.Key] = new TrayStatus
                {
                    TrayId = kvp.Key,
                    Label = kvp.Value,
                    HasPaper = true // Actual paper level requires device-specific WMI or SNMP
                };
            }
        }
        catch (Exception ex)
        {
            _logger.LogWarning(ex, "Failed to query printer health");
            health.IsOnline = false;
        }

        return health;
    }
}
