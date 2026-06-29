namespace QrintoPrintAgent.Models;

/// <summary>
/// Represents a print job received from the Qrinto backend.
/// </summary>
public class PrintJob
{
    public string JobId { get; set; } = string.Empty;
    public string OrderId { get; set; } = string.Empty;
    public string AssetUrl { get; set; } = string.Empty;
    public string MediaSize { get; set; } = string.Empty;
    public string Product { get; set; } = string.Empty;
    public int Quantity { get; set; } = 1;
    public string? TrayId { get; set; }
    public PrintSettings Settings { get; set; } = new();
    public string CustomerName { get; set; } = string.Empty;
    public DateTime ReceivedAt { get; set; } = DateTime.UtcNow;
    public PrintJobState State { get; set; } = PrintJobState.Queued;
    public string? ErrorMessage { get; set; }
    public string? LocalAssetPath { get; set; }
}

/// <summary>
/// Print job lifecycle states as defined in the architecture doc (Section 5.1).
/// </summary>
public enum PrintJobState
{
    Queued,
    Rendering,
    Spooled,
    Printing,
    Completed,
    Failed
}

/// <summary>
/// Per-job print settings, resolved from Admin UI defaults + tray mapping.
/// </summary>
public class PrintSettings
{
    public string ColorMode { get; set; } = "CMYK";
    public int Resolution { get; set; } = 1200;
    public string DuplexMode { get; set; } = "AutoDuplex";
    public string MediaType { get; set; } = "Glossy";
}
