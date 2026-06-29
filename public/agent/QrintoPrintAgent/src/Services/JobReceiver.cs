using Microsoft.Extensions.Options;
using QrintoPrintAgent.Config;
using QrintoPrintAgent.Models;

namespace QrintoPrintAgent.Services;

/// <summary>
/// Listens for incoming print jobs via WebSocket and manages the local job queue.
/// Downloads print-ready assets and enqueues for processing (Section 3.2.2).
/// </summary>
public class JobReceiver
{
    private readonly AgentConfig _config;
    private readonly ILogger<JobReceiver> _logger;
    private readonly HttpClient _httpClient;
    private readonly AuditLogger _auditLogger;
    private readonly Queue<PrintJob> _jobQueue = new();
    private readonly object _lock = new();

    public int QueueDepth
    {
        get { lock (_lock) return _jobQueue.Count; }
    }

    public JobReceiver(
        IOptions<AgentConfig> config,
        ILogger<JobReceiver> logger,
        AuditLogger auditLogger)
    {
        _config = config.Value;
        _logger = logger;
        _auditLogger = auditLogger;
        _httpClient = new HttpClient();
    }

    /// <summary>
    /// Handles an incoming print job message from the backend.
    /// Validates, downloads the asset, and enqueues.
    /// </summary>
    public async Task HandleJobAsync(PrintJob job, CancellationToken cancellationToken)
    {
        _logger.LogInformation("Received print job {JobId} for order {OrderId}", job.JobId, job.OrderId);

        // Validate required fields
        if (string.IsNullOrEmpty(job.AssetUrl) || string.IsNullOrEmpty(job.MediaSize))
        {
            _logger.LogError("Invalid job payload: missing AssetUrl or MediaSize");
            job.State = PrintJobState.Failed;
            job.ErrorMessage = "Invalid job payload";
            return;
        }

        // Download asset with retry (Section 5.2)
        job.State = PrintJobState.Queued;
        var assetPath = await DownloadAssetWithRetryAsync(job, cancellationToken);

        if (assetPath == null)
        {
            job.State = PrintJobState.Failed;
            job.ErrorMessage = "download_error";
            _logger.LogError("Failed to download asset for job {JobId} after retries", job.JobId);
            return;
        }

        job.LocalAssetPath = assetPath;
        Enqueue(job);

        await _auditLogger.LogAsync(new AuditEntry
        {
            OrderId = job.OrderId,
            Action = "job_queued",
            Metadata = new Dictionary<string, string>
            {
                ["jobId"] = job.JobId,
                ["mediaSize"] = job.MediaSize,
                ["quantity"] = job.Quantity.ToString()
            }
        });
    }

    /// <summary>
    /// Dequeues the next job for processing.
    /// </summary>
    public PrintJob? Dequeue()
    {
        lock (_lock)
        {
            return _jobQueue.Count > 0 ? _jobQueue.Dequeue() : null;
        }
    }

    public void Enqueue(PrintJob job)
    {
        lock (_lock) _jobQueue.Enqueue(job);
        _logger.LogDebug("Job {JobId} enqueued. Queue depth: {Depth}", job.JobId, QueueDepth);
    }

    /// <summary>
    /// Downloads the print-ready asset with retry logic.
    /// 3 retries with 2s delay between each (Section 5.2).
    /// </summary>
    private async Task<string?> DownloadAssetWithRetryAsync(PrintJob job, CancellationToken cancellationToken)
    {
        var retries = _config.Printer.AssetDownloadRetries;
        var delay = _config.Printer.AssetDownloadRetryDelayMs;

        for (int attempt = 1; attempt <= retries; attempt++)
        {
            try
            {
                _logger.LogDebug("Downloading asset for {JobId}, attempt {Attempt}/{Max}",
                    job.JobId, attempt, retries);

                var response = await _httpClient.GetAsync(job.AssetUrl, cancellationToken);
                response.EnsureSuccessStatusCode();

                // Save to temp directory
                var tempDir = Path.Combine(Path.GetTempPath(), "QrintoPrintAgent");
                Directory.CreateDirectory(tempDir);

                var extension = Path.GetExtension(new Uri(job.AssetUrl).AbsolutePath) ?? ".pdf";
                var filePath = Path.Combine(tempDir, $"{job.JobId}{extension}");

                var bytes = await response.Content.ReadAsByteArrayAsync(cancellationToken);
                await File.WriteAllBytesAsync(filePath, bytes, cancellationToken);

                _logger.LogInformation("Asset downloaded: {Path} ({Size} bytes)",
                    filePath, bytes.Length);
                return filePath;
            }
            catch (Exception ex) when (attempt < retries)
            {
                _logger.LogWarning(ex, "Download attempt {Attempt} failed for {JobId}. Retrying in {Delay}ms",
                    attempt, job.JobId, delay);
                await Task.Delay(delay, cancellationToken);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Download failed for {JobId} after {Max} attempts",
                    job.JobId, retries);
            }
        }

        return null;
    }
}
