<?php

/**
 * Weekly analytics configuration.
 *
 * Recipients are read from ANALYTICS_RECIPIENTS (comma-separated). If that
 * env is empty, we fall back to MAIL_FROM_ADDRESS so the report never
 * silently vanishes on a fresh install.
 */

$recipients = array_filter(array_map('trim', explode(',', (string) env('ANALYTICS_RECIPIENTS', ''))));
if (empty($recipients)) {
    $fallback = env('MAIL_FROM_ADDRESS');
    if ($fallback) $recipients = [$fallback];
}

$cc  = array_filter(array_map('trim', explode(',', (string) env('ANALYTICS_CC',  ''))));
$bcc = array_filter(array_map('trim', explode(',', (string) env('ANALYTICS_BCC', ''))));

return [
    'recipients' => $recipients,
    'cc'         => $cc,
    'bcc'        => $bcc,

    // Weekly report schedule (day + local time). Day: 1=Mon … 7=Sun.
    // Runs after the reporting window closes so the numbers are complete.
    'weekly' => [
        'day'  => (int) env('ANALYTICS_WEEKLY_DAY', 1),   // Monday
        'time' => env('ANALYTICS_WEEKLY_TIME', '07:00'),
    ],
];
