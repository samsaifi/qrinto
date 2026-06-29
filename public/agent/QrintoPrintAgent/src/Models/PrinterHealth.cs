namespace QrintoPrintAgent.Models;

/// <summary>
/// Periodic printer health snapshot sent to the backend (Section 7.1).
/// </summary>
public class PrinterHealth
{
    public bool IsOnline { get; set; }
    public string PrinterName { get; set; } = string.Empty;
    public string DriverVersion { get; set; } = string.Empty;
    public int QueueDepth { get; set; }
    public Dictionary<int, TrayStatus> Trays { get; set; } = new();
    public DateTime Timestamp { get; set; } = DateTime.UtcNow;
}

public class TrayStatus
{
    public int TrayId { get; set; }
    public string Label { get; set; } = string.Empty;
    public bool HasPaper { get; set; } = true;
    public string? PaperSize { get; set; }
}

/// <summary>
/// WebSocket message envelope for backend communication.
/// </summary>
public class AgentMessage
{
    public string Type { get; set; } = string.Empty;     // "job_result", "health", "config_sync"
    public string? JobId { get; set; }
    public PrintJobState? State { get; set; }
    public string? ErrorCode { get; set; }
    public string? ErrorMessage { get; set; }
    public PrinterHealth? Health { get; set; }
    public DateTime Timestamp { get; set; } = DateTime.UtcNow;
}

/// <summary>
/// Inbound message from the backend to the agent.
/// </summary>
public class BackendMessage
{
    public string Type { get; set; } = string.Empty;     // "print_job", "config_update", "ping"
    public PrintJob? Job { get; set; }
    public List<TrayMapping>? TrayConfig { get; set; }
}
