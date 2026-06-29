namespace QrintoPrintAgent.Models;

/// <summary>
/// Structured audit log entry (Section 7.2).
/// Written as JSON to local log files, retained 90 days.
/// </summary>
public class AuditEntry
{
    public DateTime Timestamp { get; set; } = DateTime.UtcNow;
    public string OrderId { get; set; } = string.Empty;
    public string Action { get; set; } = string.Empty;    // print_started, print_completed, print_failed, config_synced
    public int? TrayUsed { get; set; }
    public string? OperatorId { get; set; }
    public long? DurationMs { get; set; }
    public string? ErrorCode { get; set; }
    public string? ErrorDetails { get; set; }
    public Dictionary<string, string>? Metadata { get; set; }
}
