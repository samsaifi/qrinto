using System.Diagnostics;
using System.Drawing;
using System.Drawing.Printing;
using System.Management;
using Microsoft.Extensions.Options;
using QrintoPrintAgent.Config;
using QrintoPrintAgent.Models;

namespace QrintoPrintAgent.Services;

/// <summary>
/// Core printing module (Section 3.2.4).
/// Configures PrintDocument with correct tray, duplex, color, resolution,
/// renders the asset, submits to spooler, and monitors completion via WMI.
/// </summary>
public class PrintExecutor
{
    private readonly AgentConfig _config;
    private readonly ILogger<PrintExecutor> _logger;
    private readonly TrayResolver _trayResolver;
    private readonly AuditLogger _auditLogger;

    public PrintExecutor(
        IOptions<AgentConfig> config,
        ILogger<PrintExecutor> logger,
        TrayResolver trayResolver,
        AuditLogger auditLogger)
    {
        _config = config.Value;
        _logger = logger;
        _trayResolver = trayResolver;
        _auditLogger = auditLogger;
    }

    /// <summary>
    /// Executes a print job: configures printer settings, renders the asset,
    /// and submits to the Windows Print Spooler.
    /// </summary>
    public async Task<PrintJobResult> ExecuteAsync(PrintJob job, CancellationToken cancellationToken)
    {
        var sw = Stopwatch.StartNew();
        _logger.LogInformation("Executing print job {JobId} for order {OrderId}", job.JobId, job.OrderId);

        try
        {
            job.State = PrintJobState.Rendering;

            // Load the print-ready asset
            if (string.IsNullOrEmpty(job.LocalAssetPath) || !File.Exists(job.LocalAssetPath))
            {
                throw new FileNotFoundException($"Asset file not found: {job.LocalAssetPath}");
            }

            using var image = Image.FromFile(job.LocalAssetPath);

            // Configure PrintDocument
            using var printDoc = new PrintDocument();
            printDoc.PrinterSettings.PrinterName = _config.Printer.PrinterName;

            if (!printDoc.PrinterSettings.IsValid)
            {
                throw new InvalidOperationException($"Printer '{_config.Printer.PrinterName}' is not available.");
            }

            // Tray selection (Section 4.2)
            var paperSource = _trayResolver.Resolve(job);
            if (paperSource != null)
            {
                printDoc.DefaultPageSettings.PaperSource = paperSource;
            }

            // Duplex setting
            if (job.Settings.DuplexMode == "AutoDuplex" &&
                printDoc.PrinterSettings.CanDuplex)
            {
                printDoc.PrinterSettings.Duplex = Duplex.Horizontal;
            }

            // Color mode
            printDoc.DefaultPageSettings.Color = job.Settings.ColorMode == "CMYK";

            // Resolution
            foreach (PrinterResolution res in printDoc.PrinterSettings.PrinterResolutions)
            {
                if (res.X == job.Settings.Resolution)
                {
                    printDoc.DefaultPageSettings.PrinterResolution = res;
                    break;
                }
            }

            // Print copies for quantity
            printDoc.PrinterSettings.Copies = (short)Math.Max(1, job.Quantity);

            // Render handler
            var printedPages = 0;
            printDoc.PrintPage += (sender, e) =>
            {
                if (e.Graphics == null) return;

                // Scale image to fit the printable area
                var bounds = e.MarginBounds;
                var scale = Math.Min(
                    (float)bounds.Width / image.Width,
                    (float)bounds.Height / image.Height
                );
                var width = (int)(image.Width * scale);
                var height = (int)(image.Height * scale);
                var x = bounds.Left + (bounds.Width - width) / 2;
                var y = bounds.Top + (bounds.Height - height) / 2;

                e.Graphics.DrawImage(image, x, y, width, height);
                e.HasMorePages = false;
                printedPages++;
            };

            // Submit to spooler
            job.State = PrintJobState.Spooled;
            _logger.LogInformation("Submitting job {JobId} to spooler (tray: {Tray}, copies: {Qty})",
                job.JobId,
                paperSource?.SourceName ?? "default",
                job.Quantity);

            printDoc.Print();
            job.State = PrintJobState.Printing;

            // Monitor spooler for completion (Section 5.1)
            var completed = await WaitForSpoolerCompletionAsync(job, cancellationToken);

            sw.Stop();

            if (completed)
            {
                job.State = PrintJobState.Completed;
                _logger.LogInformation("Job {JobId} completed in {Duration}ms", job.JobId, sw.ElapsedMilliseconds);

                await _auditLogger.LogAsync(new AuditEntry
                {
                    OrderId = job.OrderId,
                    Action = "print_completed",
                    TrayUsed = paperSource?.RawKind,
                    DurationMs = sw.ElapsedMilliseconds
                });

                return new PrintJobResult(true, sw.ElapsedMilliseconds);
            }
            else
            {
                job.State = PrintJobState.Failed;
                job.ErrorMessage = "spooler_timeout";

                await _auditLogger.LogAsync(new AuditEntry
                {
                    OrderId = job.OrderId,
                    Action = "print_failed",
                    ErrorCode = "spooler_timeout",
                    DurationMs = sw.ElapsedMilliseconds
                });

                return new PrintJobResult(false, sw.ElapsedMilliseconds, "spooler_timeout");
            }
        }
        catch (Exception ex)
        {
            sw.Stop();
            _logger.LogError(ex, "Print execution failed for job {JobId}", job.JobId);
            job.State = PrintJobState.Failed;
            job.ErrorMessage = ex.Message;

            await _auditLogger.LogAsync(new AuditEntry
            {
                OrderId = job.OrderId,
                Action = "print_failed",
                ErrorCode = "execution_error",
                ErrorDetails = ex.Message,
                DurationMs = sw.ElapsedMilliseconds
            });

            return new PrintJobResult(false, sw.ElapsedMilliseconds, ex.Message);
        }
        finally
        {
            // Clean up local asset (Section 6: no customer data persists)
            CleanupAsset(job);
        }
    }

    /// <summary>
    /// Monitors the Windows Print Spooler via WMI for job completion or error.
    /// Times out after SpoolerTimeoutMs (default 120s) per Section 5.2.
    /// </summary>
    private async Task<bool> WaitForSpoolerCompletionAsync(PrintJob job, CancellationToken cancellationToken)
    {
        var timeout = TimeSpan.FromMilliseconds(_config.Printer.SpoolerTimeoutMs);
        var sw = Stopwatch.StartNew();

        while (sw.Elapsed < timeout && !cancellationToken.IsCancellationRequested)
        {
            try
            {
                // Query WMI for active print jobs on our printer
                using var searcher = new ManagementObjectSearcher(
                    $"SELECT * FROM Win32_PrintJob WHERE Name LIKE '%{_config.Printer.PrinterName}%'"
                );

                var jobs = searcher.Get();
                bool jobFound = false;

                foreach (ManagementObject printJob in jobs)
                {
                    var status = printJob["StatusMask"]?.ToString();
                    var jobStatus = printJob["JobStatus"]?.ToString();

                    if (jobStatus?.Contains("Error") == true ||
                        jobStatus?.Contains("Offline") == true)
                    {
                        _logger.LogWarning("Printer error detected: {Status}", jobStatus);
                        return false;
                    }

                    jobFound = true;
                }

                // If no jobs found in spooler, it's either completed or never started
                if (!jobFound && sw.ElapsedMilliseconds > 2000)
                {
                    return true; // Job completed and was removed from spooler
                }
            }
            catch (ManagementException ex)
            {
                _logger.LogWarning(ex, "WMI query error during spooler monitoring");
            }

            await Task.Delay(500, cancellationToken);
        }

        return false; // Timeout
    }

    /// <summary>
    /// Deletes the local asset file after printing (Section 6).
    /// </summary>
    private void CleanupAsset(PrintJob job)
    {
        try
        {
            if (!string.IsNullOrEmpty(job.LocalAssetPath) && File.Exists(job.LocalAssetPath))
            {
                File.Delete(job.LocalAssetPath);
                _logger.LogDebug("Cleaned up asset: {Path}", job.LocalAssetPath);
            }
        }
        catch (Exception ex)
        {
            _logger.LogWarning(ex, "Failed to clean up asset: {Path}", job.LocalAssetPath);
        }
    }
}

public record PrintJobResult(bool Success, long DurationMs, string? ErrorMessage = null);
