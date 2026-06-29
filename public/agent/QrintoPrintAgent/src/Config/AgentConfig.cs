namespace QrintoPrintAgent.Config;

/// <summary>
/// Root configuration model, bound from appsettings.json.
/// </summary>
public class AgentConfig
{
    public string StoreId { get; set; } = string.Empty;
    public string ApiKey { get; set; } = string.Empty;
    public ServerConfig Server { get; set; } = new();
    public PrinterConfig Printer { get; set; } = new();
    public LoggingConfig Logging { get; set; } = new();
}

public class ServerConfig
{
    public string BackendUrl { get; set; } = "https://api.qrinto.com";
    public string WebSocketUrl { get; set; } = "wss://api.qrinto.com/ws/agent";
    public int PollingIntervalMs { get; set; } = 5000;
    public int HeartbeatIntervalMs { get; set; } = 30000;
    public int ReconnectMaxDelayMs { get; set; } = 60000;
}

public class PrinterConfig
{
    public string PrinterName { get; set; } = "Noritsu 931-BL";
    public string ColorMode { get; set; } = "CMYK";
    public int Resolution { get; set; } = 1200;
    public string DuplexMode { get; set; } = "AutoDuplex";
    public string MediaType { get; set; } = "Glossy";
    public int SpoolerTimeoutMs { get; set; } = 120000;
    public int AssetDownloadRetries { get; set; } = 3;
    public int AssetDownloadRetryDelayMs { get; set; } = 2000;
}

public class LoggingConfig
{
    public string LogDirectory { get; set; } = "logs";
    public int RetentionDays { get; set; } = 90;
}
