<?php

namespace App\Services;

use Illuminate\Support\Str;
use ZipArchive;

/**
 * Builds a ZIP-of-CSVs "Excel workbook" from a pre-computed analytics
 * payload. Each CSV becomes a tab when opened in Excel via File → Open.
 * We ship CSVs (not a real XLSX) because the project has no XLSX package
 * installed, and this matches DashboardController's existing CSV pattern.
 */
class WeeklyAnalyticsExport
{
    public function buildZip(array $data): string
    {
        $dir = storage_path('app/tmp-analytics');
        if (!is_dir($dir)) mkdir($dir, 0775, true);

        $zipPath = $dir . DIRECTORY_SEPARATOR
            . 'weekly-' . now()->format('Ymd-His') . '-' . Str::random(6) . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Could not create weekly-analytics ZIP.');
        }

        $zip->addFromString('01_Summary.csv',      $this->csv($this->summaryRows($data)));
        $zip->addFromString('02_Orders.csv',       $this->csv($this->ordersRows($data)));
        $zip->addFromString('03_PrintLogs.csv',    $this->csv($this->printLogsRows($data)));
        $zip->addFromString('04_Kiosks.csv',       $this->csv($this->kiosksRows($data)));
        $zip->addFromString('05_StoreSummary.csv', $this->csv($this->storeSummaryRows($data)));
        $zip->addFromString('06_DailySummary.csv', $this->csv($this->dailyRows($data)));
        $zip->addFromString('07_Payments.csv',     $this->csv($this->paymentsRows($data)));

        $zip->close();
        return $zipPath;
    }

    /* ── row builders ── */

    private function summaryRows(array $d): array
    {
        $s = $d['summary'];
        return [
            ['Weekly Analytics Report'],
            ['Period', $d['period']['label']],
            ['Generated at', now()->format('Y-m-d H:i:s')],
            [],
            ['Metric', 'Value'],
            ['Total Orders',      $s['total_orders']],
            ['Total Revenue',     $s['total_revenue']],
            ['Total Pending Amt', $s['total_pending_amt']],
            ['Total Print Events',$s['total_print_events']],
            ['Total Picked Up',   $s['total_picked_up']],
            ['Total Pending',     $s['total_pending']],
            ['Total Kiosks',      $s['total_kiosks']],
            ['Active Kiosks',     $s['active_kiosks']],
        ];
    }

    private function ordersRows(array $d): array
    {
        $o = $d['orders'];
        $rows = [
            ['Order Analytics'],
            ['Metric', 'Value'],
            ['Total Orders',      $o['total_orders']],
            ['New',               $o['new_orders']],
            ['Printing',          $o['printing_orders']],
            ['Ready for Pickup',  $o['ready_orders']],
            ['Picked Up',         $o['picked_up_orders']],
            ['Cancelled',         $o['cancelled_orders']],
            ['Pending Orders',    $o['pending_orders']],
            ['Paid Orders',       $o['paid_orders']],
            ['Online Amount',     $o['online_amount']],
            ['Cash Amount',       $o['cash_amount']],
            ['Pending Amount',    $o['pending_amount']],
            ['Paid Amount',       $o['paid_amount']],
            ['Total Revenue',     $o['total_revenue']],
            ['Gross Order Value', $o['total_value']],
        ];
        return $rows;
    }

    private function printLogsRows(array $d): array
    {
        $p = $d['print_logs'];
        $rows = [
            ['Print Logs Analytics'],
            ['Metric', 'Value'],
            ['Total Print Events',    $p['total_logs']],
            ['Distinct Orders',       $p['total_orders']],
            ['Success',               $p['success_count']],
            ['Failed',                $p['failed_count']],
            ['Retried',               $p['retried_count']],
            ['Stage: New',            $p['new_orders']],
            ['Stage: Printing',       $p['printing_orders']],
            ['Stage: Ready',          $p['ready_orders']],
            ['Stage: Picked Up',      $p['picked_up_orders']],
            [],
            ['Printer', 'Events', 'Distinct Orders'],
        ];
        foreach ($d['printers'] as $r) {
            $rows[] = [$r->printer_name, $r->events, $r->orders];
        }
        $rows[] = [];
        $rows[] = ['Size', 'Media', 'gsm', 'Events'];
        foreach ($d['media'] as $r) {
            $rows[] = [$r->size, $r->media, $r->gsm, $r->events];
        }
        return $rows;
    }

    private function kiosksRows(array $d): array
    {
        $k = $d['kiosks'];
        $rows = [
            ['Kiosk Analytics'],
            ['Total Kiosks',    $k['totals']['total']],
            ['Active Kiosks',   $k['totals']['active']],
            ['Inactive Kiosks', $k['totals']['inactive']],
            [],
            ['Store', 'Kiosks', 'Active', 'Total Amount'],
        ];
        foreach ($k['per_store'] as $r) {
            $rows[] = [$r->store_name, $r->kiosk_count, $r->active_count, $r->total_amount];
        }
        $rows[] = [];
        $rows[] = ['Kiosk ID', 'Store ID', 'Printer Tray', 'Print Size', 'Active', 'Print Events (week)'];
        foreach ($k['kiosk_activity'] as $r) {
            $rows[] = [$r->id, $r->store_id, $r->printer_tray, $r->print_size, $r->kiosk ? 'Yes' : 'No', $r->events];
        }
        return $rows;
    }

    private function storeSummaryRows(array $d): array
    {
        $rows = [[
            'Store', 'Test?', 'Active?', 'Orders', 'New', 'Printing', 'Ready', 'Picked Up',
            'Revenue', 'Pending Amt', 'Cash Amt', 'Online Amt', 'Print Events',
        ]];
        foreach ($d['per_store'] as $s) {
            $rows[] = [
                $s['store_name'],
                $s['is_test']   ? 'Yes' : 'No',
                $s['is_active'] ? 'Yes' : 'No',
                $s['total_orders'], $s['new_orders'], $s['printing_orders'],
                $s['ready_orders'], $s['picked_up_orders'],
                $s['revenue'], $s['pending_amount'], $s['cash_amount'], $s['online_amount'],
                $s['print_events'],
            ];
        }
        return $rows;
    }

    private function dailyRows(array $d): array
    {
        $rows = [[
            'Date', 'Orders', 'Revenue', 'New', 'Printing', 'Ready', 'Picked Up', 'Print Events',
        ]];
        foreach ($d['daily'] as $r) {
            $rows[] = [
                $r['date'], $r['orders'], $r['revenue'], $r['new_orders'],
                $r['printing_orders'], $r['ready_orders'], $r['picked_up_orders'],
                $r['print_events'],
            ];
        }
        return $rows;
    }

    private function paymentsRows(array $d): array
    {
        $o = $d['orders'];
        return [
            ['Payment Analytics'],
            ['Metric', 'Value'],
            ['Total Revenue (paid)', $o['total_revenue']],
            ['Paid Amount',          $o['paid_amount']],
            ['Pending Amount',       $o['pending_amount']],
            ['Online Payment Amt',   $o['online_amount']],
            ['Cash Payment Amt',     $o['cash_amount']],
            ['Paid Order Count',     $o['paid_orders']],
            ['Pending Order Count',  $o['pending_orders']],
        ];
    }

    /**
     * Encode a 2D array as a UTF-8 CSV string with BOM (so Excel opens
     * international text cleanly).
     */
    private function csv(array $rows): string
    {
        $fh = fopen('php://temp', 'r+');
        fwrite($fh, chr(0xEF) . chr(0xBB) . chr(0xBF));
        foreach ($rows as $row) {
            fputcsv($fh, $row);
        }
        rewind($fh);
        return stream_get_contents($fh);
    }
}
