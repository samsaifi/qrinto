<?php

namespace App\Mail;

use App\Services\WeeklyAnalyticsExport;
use App\Services\WeeklyAnalyticsService;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Weekly analytics email. Sent synchronously — the scheduler fires once
 * a week, so we don't need a queue worker to deliver it.
 */
class WeeklyAnalyticsReport extends Mailable
{
    use SerializesModels;

    public array $summary;
    public array $orders;
    public array $printLogs;
    public array $kiosks;
    public $perStore;
    public $daily;
    public $printers;
    public $media;
    public array $observations;
    public array $period;

    public function __construct(
        private WeeklyAnalyticsService $analytics,
    ) {
        // Front-load every section on construction. The mailable is
        // serialised for queue, so views never re-query.
        $this->period       = $analytics->period();
        $this->summary      = $analytics->summary();
        $this->orders       = $analytics->orders();
        $this->printLogs    = $analytics->printLogs();
        $this->kiosks       = $analytics->kiosks();
        $this->perStore     = $analytics->perStore();
        $this->daily        = $analytics->daily();
        $this->printers     = $analytics->printerBreakdown();
        $this->media        = $analytics->mediaBreakdown();
        $this->observations = $analytics->observations();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Weekly Analytics Report — ' . $this->period['label'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.weekly-analytics',
        );
    }

    public function attachments(): array
    {
        // Multi-"sheet" ZIP: one CSV per section, Excel opens each as a tab.
        $zipPath = app(WeeklyAnalyticsExport::class)->buildZip([
            'period'       => $this->period,
            'summary'      => $this->summary,
            'orders'       => $this->orders,
            'print_logs'   => $this->printLogs,
            'kiosks'       => $this->kiosks,
            'per_store'    => $this->perStore,
            'daily'        => $this->daily,
            'printers'     => $this->printers,
            'media'        => $this->media,
            'observations' => $this->observations,
        ]);

        $filename = 'weekly-analytics-' . $this->period['start']->format('Y-m-d')
            . '_to_' . $this->period['end']->format('Y-m-d') . '.zip';

        return [
            Attachment::fromPath($zipPath)
                ->as($filename)
                ->withMime('application/zip'),
        ];
    }
}
