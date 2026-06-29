using System.Drawing.Printing;
using Microsoft.Extensions.Options;
using QrintoPrintAgent.Config;
using QrintoPrintAgent.Models;

namespace QrintoPrintAgent.Services;

/// <summary>
/// Maps Qrinto media sizes to physical printer trays (Section 3.2.3, 4.1-4.3).
/// Validates tray configuration against the installed printer driver on startup.
/// </summary>
public class TrayResolver
{
    private readonly AgentConfig _config;
    private readonly ILogger<TrayResolver> _logger;
    private List<TrayMapping> _trayMappings = new();
    private Dictionary<int, PaperSource>? _physicalTrays;

    public TrayResolver(IOptions<AgentConfig> config, ILogger<TrayResolver> logger)
    {
        _config = config.Value;
        _logger = logger;
    }

    /// <summary>
    /// Enumerates physical trays from the printer driver and validates
    /// configured mappings against them.
    /// </summary>
    public void Initialize()
    {
        _physicalTrays = new Dictionary<int, PaperSource>();

        try
        {
            var settings = new PrinterSettings { PrinterName = _config.Printer.PrinterName };

            if (!settings.IsValid)
            {
                _logger.LogError("Printer '{Name}' not found or not valid", _config.Printer.PrinterName);
                return;
            }

            foreach (PaperSource source in settings.PaperSources)
            {
                _physicalTrays[source.RawKind] = source;
                _logger.LogInformation("Found tray: {Kind} = {Name}", source.RawKind, source.SourceName);
            }

            _logger.LogInformation("Discovered {Count} trays on {Printer}",
                _physicalTrays.Count, _config.Printer.PrinterName);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to enumerate printer trays");
        }
    }

    /// <summary>
    /// Updates the tray mapping configuration from a backend sync.
    /// Validates each mapping against physical trays.
    /// </summary>
    public void UpdateMappings(List<TrayMapping> mappings)
    {
        _trayMappings = mappings;
        _logger.LogInformation("Tray mappings updated: {Count} entries", mappings.Count);

        // Validate each mapping
        foreach (var mapping in mappings)
        {
            if (_physicalTrays != null && !_physicalTrays.ContainsKey(mapping.TrayId))
            {
                _logger.LogWarning(
                    "Tray {TrayId} (for {Product} {Media}) not found on physical printer. Will use default tray.",
                    mapping.TrayId, mapping.Product, mapping.MediaSize);
            }
        }
    }

    /// <summary>
    /// Resolves the PaperSource for a given print job based on its media size.
    /// Returns null if no mapping is found (uses default tray).
    /// </summary>
    public PaperSource? Resolve(PrintJob job)
    {
        // Find the mapping for this media size + product combination
        var mapping = _trayMappings.FirstOrDefault(m =>
            m.MediaSize == job.MediaSize && m.Product == job.Product);

        if (mapping == null)
        {
            _logger.LogWarning("No tray mapping for {Product} / {Media}. Using default tray.",
                job.Product, job.MediaSize);
            return null;
        }

        if (_physicalTrays != null && _physicalTrays.TryGetValue(mapping.TrayId, out var source))
        {
            _logger.LogDebug("Resolved tray {TrayId} ({Name}) for {Product} / {Media}",
                mapping.TrayId, source.SourceName, job.Product, job.MediaSize);
            return source;
        }

        _logger.LogWarning("Tray {TrayId} not available on printer. Falling back to default.",
            mapping.TrayId);
        return null;
    }

    /// <summary>
    /// Returns all physical trays detected on the printer (for health reporting).
    /// </summary>
    public Dictionary<int, string> GetPhysicalTrays()
    {
        if (_physicalTrays == null) return new Dictionary<int, string>();
        return _physicalTrays.ToDictionary(kvp => kvp.Key, kvp => kvp.Value.SourceName);
    }
}
