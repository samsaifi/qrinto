<?php

namespace App\Services;

use App\Models\LocalPrintLog;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

/**
 * Centralised logging for the two local-print flows (/print/pdf/check and
 * /print/own-design/sizes/customizer/preview). Both flows call this ONE service
 * so every attempt produces the same consistent, auditable record.
 *
 * Lifecycle:
 *   start()    → create ONE row (status = sending) before the PrintTrays call
 *   complete() → update that SAME row with the outcome (completed | failed)
 *
 * This service only records what the existing flow already sends to PrintTrays.
 * It never alters the print configuration or output.
 */
class LocalPrintLogService
{
    /** Status values, mirroring the spec's lifecycle. */
    public const PENDING   = 'pending';
    public const SENDING   = 'sending';
    public const SENT      = 'sent';
    public const PRINTING  = 'printing';
    public const COMPLETED = 'completed';
    public const FAILED    = 'failed';
    public const CANCELLED = 'cancelled';

    /**
     * Create the initial log record from the client payload + server request
     * context. Returns the persisted LocalPrintLog (the id is the job reference).
     *
     * @param array   $payload  Everything the page knows about this print.
     * @param Request $request  The HTTP request (for IP / UA / user / store).
     */
    public function start(array $payload, Request $request): LocalPrintLog
    {
        $user  = $request->user();
        $store = ($user && $user->store_id) ? $user->store : null;

        $requestPayload  = Arr::get($payload, 'request_payload', []);
        $printerSettings = Arr::get($payload, 'printer_settings', []);
        $designSettings  = Arr::get($payload, 'design_settings', []);
        $fileInfo        = Arr::get($payload, 'file', []);

        $ua     = (string) $request->userAgent();
        $client = $this->parseUserAgent($ua);

        // IP-based geolocation (fail-soft; skipped for private/localhost IPs).
        $geo = $this->resolveGeo($request->ip());

        $source = (string) Arr::get($payload, 'source', 'local');

        // Where the print came from. `web` = browser via LPLog on the public
        // print pages; `standalone` = the kiosk desktop app via the API. Anything
        // else gets normalised back to `web` so a bad client cannot write junk.
        $origin = (string) Arr::get($payload, 'origin', LocalPrintLog::ORIGIN_WEB);
        if (!in_array($origin, array_keys(LocalPrintLog::ORIGINS), true)) {
            $origin = LocalPrintLog::ORIGIN_WEB;
        }

        $log = LocalPrintLog::create([
            'store_id'   => $store?->id,
            'user_id'    => $user?->id,

            'source'     => $source,
            'origin'     => $origin,
            'source_url' => (string) Arr::get($payload, 'source_url', $request->headers->get('referer')),
            'print_type' => Arr::get($payload, 'print_type', Arr::get($fileInfo, 'type')),
            'status'     => self::SENDING,

            'order_id'    => Arr::get($designSettings, 'order_id'),
            'design_id'   => Arr::get($designSettings, 'design_id'),
            'template_id' => Arr::get($designSettings, 'template_id'),
            'product_id'  => Arr::get($designSettings, 'product_id'),

            'printer_name'  => Arr::get($printerSettings, 'printer_name'),
            'printer_id'    => Arr::get($printerSettings, 'printer_id'),
            'printer_model' => Arr::get($printerSettings, 'printer_model'),
            'tray_key'      => Arr::get($printerSettings, 'tray_key'),
            'tray_label'    => Arr::get($printerSettings, 'tray_label'),
            'paper_source'  => Arr::get($printerSettings, 'paper_source'),
            'paper_size'    => Arr::get($printerSettings, 'paper_size'),
            'media_size'    => Arr::get($printerSettings, 'media_size'),
            'orientation'   => Arr::get($printerSettings, 'orientation'),
            'color_mode'    => Arr::get($printerSettings, 'color_mode'),
            'duplex'        => Arr::get($printerSettings, 'duplex'),
            'scale_mode'    => Arr::get($printerSettings, 'scale_mode'),
            'scale_percentage' => Arr::get($printerSettings, 'scale_percentage'),
            'copies'        => (int) (Arr::get($printerSettings, 'copies', 1) ?: 1),

            'file_url'        => Arr::get($fileInfo, 'url'),
            'file_name'       => Arr::get($fileInfo, 'name'),
            'file_type'       => Arr::get($fileInfo, 'type'),
            'file_size'       => Arr::get($fileInfo, 'size'),
            'page_count'      => Arr::get($fileInfo, 'pages'),
            'page_dimensions' => Arr::get($fileInfo, 'dimensions'),

            'request_payload'  => $this->scrub($requestPayload),
            'printer_settings' => $this->scrub($printerSettings),
            'design_settings'  => $this->scrub($designSettings),
            'system_information' => [
                'ip'          => $request->ip(),
                'user_agent'  => $ua,
                'browser'     => $client['browser'],
                'os'          => $client['os'],
                'device'      => Arr::get($payload, 'device_name'),
                'session_id'  => $request->hasSession() ? $request->session()->getId() : null,
                'request_url' => $request->fullUrl(),
                'referrer'    => $request->headers->get('referer'),
                'environment' => app()->environment(),
                'geo'         => $geo,
                'store' => $store ? [
                    'id'       => $store->id,
                    'name'     => $store->store_name ?? null,
                    'code'     => $store->store_code ?? null,
                    'owner'    => $store->owner_name ?? ($store->contact_name ?? null),
                    'email'    => $store->email ?? null,
                    'location' => $store->address ?? ($store->location ?? null),
                ] : null,
                'user' => $user ? [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ] : null,
            ],

            'ip_address'       => $request->ip(),
            'user_agent'       => mb_substr($ua, 0, 512),
            'browser'          => $client['browser'],
            'operating_system' => $client['os'],
            'device_name'      => Arr::get($payload, 'device_name'),
            'session_id'       => $request->hasSession() ? $request->session()->getId() : null,

            'geo_city'         => $geo['city'] ?? null,
            'geo_region'       => $geo['region'] ?? null,
            'geo_country'      => $geo['country'] ?? null,
            'geo_country_code' => $geo['country_code'] ?? null,

            'requested_at' => now(),
            'started_at'   => now(),
        ]);

        return $log;
    }

    /**
     * Update the SAME record with the print outcome. Never creates a new row.
     */
    public function complete(LocalPrintLog $log, array $result): LocalPrintLog
    {
        $ok = (bool) Arr::get($result, 'ok', false);

        $attrs = [
            'status'           => $ok ? self::COMPLETED : self::FAILED,
            'response_payload' => $this->scrub(Arr::get($result, 'response')),
            'error_message'    => $ok ? null : (string) Arr::get($result, 'error'),
            'printtrays_job_id'=> Arr::get($result, 'job_id'),
        ];

        if ($ok) {
            $attrs['completed_at'] = now();
        } else {
            $attrs['failed_at'] = now();
        }

        // Duration: prefer a client-measured value, else compute from started_at.
        $duration = Arr::get($result, 'duration_ms');
        if ($duration === null && $log->started_at) {
            $duration = now()->diffInMilliseconds($log->started_at);
        }
        $attrs['duration_ms'] = $duration !== null ? (int) $duration : null;

        $log->update($attrs);

        return $log;
    }

    /**
     * Remove obviously sensitive keys (tokens/passwords/secrets) from any
     * payload before it is persisted. Recurses through nested arrays.
     */
    protected function scrub($value)
    {
        if (!is_array($value)) {
            return $value;
        }

        $blocked = ['token', 'password', 'secret', 'authorization', 'auth', 'apikey', 'api_key', 'certificate', 'signature'];

        $out = [];
        foreach ($value as $k => $v) {
            $key = is_string($k) ? strtolower($k) : $k;
            if (is_string($key) && in_array($key, $blocked, true)) {
                continue;
            }
            $out[$k] = is_array($v) ? $this->scrub($v) : $v;
        }
        return $out;
    }

    /**
     * Resolve city / country from an IP via a free, keyless geo API (ipwho.is).
     * Returns null when the IP is private/reserved/localhost or the lookup fails
     * — geolocation must never slow down or break the print flow, so a short
     * timeout is used and every error is swallowed.
     *
     * @return array|null  ['city','region','country','country_code'] or null
     */
    protected function resolveGeo(?string $ip): ?array
    {
        if (!$ip) {
            return null;
        }

        // Only public/routable IPs can be geolocated. Localhost and LAN IPs
        // (127.0.0.1, 192.168.x, 10.x, 172.16-31.x, ::1 …) are skipped.
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return null;
        }

        try {
            $resp = \Illuminate\Support\Facades\Http::timeout(2)
                ->retry(1, 100)
                ->acceptJson()
                ->get("https://ipwho.is/{$ip}", [
                    'fields' => 'success,city,region,country,country_code',
                ]);

            if (!$resp->ok()) {
                return null;
            }

            $data = $resp->json();
            if (empty($data['success'])) {
                return null;
            }

            return [
                'city'         => $data['city'] ?? null,
                'region'       => $data['region'] ?? null,
                'country'      => $data['country'] ?? null,
                'country_code' => $data['country_code'] ?? null,
            ];
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    /**
     * Very small UA sniff — enough to label the row (browser + OS) without a
     * third-party dependency.
     */
    protected function parseUserAgent(string $ua): array
    {
        $browser = 'Unknown';
        $os = 'Unknown';

        $browsers = [
            'Edg' => 'Edge', 'OPR' => 'Opera', 'Opera' => 'Opera',
            'Chrome' => 'Chrome', 'Firefox' => 'Firefox',
            'Safari' => 'Safari', 'MSIE' => 'Internet Explorer', 'Trident' => 'Internet Explorer',
        ];
        foreach ($browsers as $needle => $label) {
            if (stripos($ua, $needle) !== false) { $browser = $label; break; }
        }

        $systems = [
            'Windows NT 10' => 'Windows 10/11', 'Windows' => 'Windows',
            'Mac OS X' => 'macOS', 'Android' => 'Android',
            'iPhone' => 'iOS', 'iPad' => 'iPadOS', 'Linux' => 'Linux',
        ];
        foreach ($systems as $needle => $label) {
            if (stripos($ua, $needle) !== false) { $os = $label; break; }
        }

        return ['browser' => $browser, 'os' => $os];
    }
}
