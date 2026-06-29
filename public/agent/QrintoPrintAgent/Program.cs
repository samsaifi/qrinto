using Serilog;
using Serilog.Formatting.Compact;
using QrintoPrintAgent.Config;
using QrintoPrintAgent.Core;
using QrintoPrintAgent.Services;

// ── Configure Serilog ──
Log.Logger = new LoggerConfiguration()
    .MinimumLevel.Information()
    .WriteTo.Console()
    .WriteTo.File(
        new CompactJsonFormatter(),
        path: "logs/agent-.log",
        rollingInterval: RollingInterval.Day,
        retainedFileCountLimit: 90)
    .CreateLogger();

try
{
    Log.Information("Starting Qrinto Print Agent");

    var builder = Host.CreateApplicationBuilder(args);

    // Use Windows Service lifetime (runs as background service, no console window)
    builder.Services.AddWindowsService(options =>
    {
        options.ServiceName = "QrintoPrintAgent";
    });

    // Bind configuration
    builder.Services.Configure<AgentConfig>(
        builder.Configuration.GetSection("Agent"));

    // Register services (singleton — shared state across the agent lifecycle)
    builder.Services.AddSingleton<AuditLogger>();
    builder.Services.AddSingleton<ConnectionManager>();
    builder.Services.AddSingleton<JobReceiver>();
    builder.Services.AddSingleton<TrayResolver>();
    builder.Services.AddSingleton<PrintExecutor>();
    builder.Services.AddSingleton<StatusReporter>();

    // Register the background worker
    builder.Services.AddHostedService<PrintAgentWorker>();

    // Use Serilog
    builder.Services.AddSerilog();

    var host = builder.Build();
    await host.RunAsync();
}
catch (Exception ex)
{
    Log.Fatal(ex, "Print Agent terminated unexpectedly");
}
finally
{
    await Log.CloseAndFlushAsync();
}
