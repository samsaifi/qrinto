namespace QrintoPrintAgent.Models;

/// <summary>
/// Maps a Qrinto media size to a physical printer tray.
/// Synced from the Admin UI Printer Settings screen.
/// </summary>
public class TrayMapping
{
    public string Product { get; set; } = string.Empty;   // "Cards" or "Magnets"
    public string MediaSize { get; set; } = string.Empty;  // e.g., "Small 3.5×5\" Folded"
    public int TrayId { get; set; }                        // Physical tray number (1-4)
    public string TrayLabel { get; set; } = string.Empty;  // e.g., "Upper Cassette"
}
