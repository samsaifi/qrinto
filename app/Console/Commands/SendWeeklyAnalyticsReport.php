<?php

namespace App\Console\Commands;

use App\Mail\WeeklyAnalyticsReport;
use App\Services\WeeklyAnalyticsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Manually or automatically send the weekly analytics report.
 *
 *   php artisan analytics:weekly-report                       # previous 7 completed days → configured recipients
 *   php artisan analytics:weekly-report --to=you@example.com  # override recipients (comma-separated)
 *   php artisan analytics:weekly-report --from=2026-08-14 --until=2026-08-20   # historical window for testing
 *   php artisan analytics:weekly-report --dry-run             # compute + preview, no email sent
 */
class SendWeeklyAnalyticsReport extends Command
{
    protected $signature = 'analytics:weekly-report
                            {--to=       : Comma-separated recipient override}
                            {--cc=       : Comma-separated CC override}
                            {--bcc=      : Comma-separated BCC override}
                            {--from=     : Start date (YYYY-MM-DD)}
                            {--until=    : End date (YYYY-MM-DD, inclusive)}
                            {--dry-run   : Compute but do not send}';

    protected $description = 'Send the weekly analytics email (Orders + Print Logs + Kiosks + Stores + Payments) with an Excel attachment.';

    public function handle(): int
    {
        // Resolve the reporting window.
        $from  = $this->option('from');
        $until = $this->option('until');
        $service = ($from && $until)
            ? WeeklyAnalyticsService::forRange($from, $until)
            : WeeklyAnalyticsService::previousWeek();

        $period = $service->period();
        $this->info('Reporting period: ' . $period['label']);

        // Resolve recipients - CLI --to wins, else config.
        $to = $this->option('to');
        $recipients = $to
            ? array_filter(array_map('trim', explode(',', $to)))
            : (array) config('analytics.recipients', []);

        if (empty($recipients)) {
            $this->error('No recipients configured. Set ANALYTICS_RECIPIENTS in .env or pass --to=you@example.com');
            return self::FAILURE;
        }
        $this->info('Recipients: ' . implode(', ', $recipients));

        if ($this->option('dry-run')) {
            $summary = $service->summary();
            $this->table(['Metric', 'Value'], [
                ['Total Orders',       $summary['total_orders']],
                ['Total Revenue',      $summary['total_revenue']],
                ['Print Events',       $summary['total_print_events']],
                ['Picked Up',          $summary['total_picked_up']],
                ['Pending',            $summary['total_pending']],
                ['Active Kiosks',      $summary['active_kiosks']],
            ]);
            $this->info('DRY RUN - no email sent.');
            return self::SUCCESS;
        }

        $cc  = $this->option('cc')  ? array_filter(array_map('trim', explode(',', $this->option('cc'))))  : (array) config('analytics.cc',  []);
        $bcc = $this->option('bcc') ? array_filter(array_map('trim', explode(',', $this->option('bcc')))) : (array) config('analytics.bcc', []);

        $pending = Mail::to($recipients);
        if (!empty($cc))  $pending->cc($cc);
        if (!empty($bcc)) $pending->bcc($bcc);
        $pending->send(new WeeklyAnalyticsReport($service));

        $this->info('Weekly analytics report sent - to: ' . count($recipients) . ', cc: ' . count($cc) . ', bcc: ' . count($bcc));
        return self::SUCCESS;
    }
}
