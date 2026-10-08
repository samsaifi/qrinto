<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LocalPrintLog;
use App\Services\LocalPrintLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * REST API for local-print audit logs.
 *
 * Serves the SAME data that /print/pdf/check writes via the LPLog wrapper, but
 * in a format the standalone kiosk/desktop app can both READ (list + detail)
 * and WRITE (create + complete). The `origin` field tells callers whether a
 * row came from the web page or from the standalone software.
 */
class LocalPrintLogApiController extends Controller
{
    /**
     * GET /api/v1/local-print-logs
     *
     * Paginated list for the kiosk app. Supports the same filters as the admin
     * screen plus `origin` so the client can request its own prints only.
     */
    public function index(Request $request): JsonResponse
    {
        $query = LocalPrintLog::with(['store:id,store_name,store_code', 'user:id,name,email'])
            ->orderByDesc('created_at');

        // Scalar filters (exact match).
        foreach ([
            'origin', 'status', 'source', 'store_id', 'user_id',
            'printer_name', 'printtrays_job_id',
        ] as $column) {
            if ($request->filled($column)) {
                $query->where($column, $request->input($column));
            }
        }

        // Free-text (file name / printer / IP).
        if ($request->filled('search')) {
            $q = trim((string) $request->input('search'));
            $query->where(function ($w) use ($q) {
                $w->where('file_name', 'like', "%{$q}%")
                    ->orWhere('printer_name', 'like', "%{$q}%")
                    ->orWhere('ip_address', 'like', "%{$q}%");
            });
        }

        // Date range on created_at.
        if ($request->filled('date_start')) {
            $query->where('created_at', '>=', now()->parse($request->input('date_start'))->startOfDay());
        }
        if ($request->filled('date_end')) {
            $query->where('created_at', '<=', now()->parse($request->input('date_end'))->endOfDay());
        }

        $perPage = min(max((int) $request->input('per_page', 25), 1), 100);
        $logs    = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'ok'   => true,
            'data' => collect($logs->items())->map(fn (LocalPrintLog $l) => $this->format($l)),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'per_page'     => $logs->perPage(),
                'last_page'    => $logs->lastPage(),
                'total'        => $logs->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/local-print-logs/{id}
     *
     * Full detail including scrubbed payload snapshots.
     */
    public function show(LocalPrintLog $localPrintLog): JsonResponse
    {
        $localPrintLog->load(['store', 'user']);
        return response()->json([
            'ok'   => true,
            'data' => $this->format($localPrintLog, true),
        ]);
    }

    /**
     * POST /api/v1/local-print-logs
     *
     * The standalone kiosk app calls this BEFORE sending a job to PrintTrays,
     * exactly like the browser's LPLog.start(). `origin` defaults to
     * `standalone` here because the web path has its own endpoint.
     */
    public function store(Request $request, LocalPrintLogService $service): JsonResponse
    {
        $payload = $request->validate([
            'origin'           => 'nullable|string|in:web,standalone',
            'source'           => 'nullable|string|max:60',
            'source_url'       => 'nullable|string|max:255',
            'print_type'       => 'nullable|string|max:40',
            'device_name'      => 'nullable|string|max:160',
            'file'             => 'nullable|array',
            'request_payload'  => 'nullable|array',
            'printer_settings' => 'nullable|array',
            'design_settings'  => 'nullable|array',
        ]);

        // Standalone is the sensible default on this endpoint; the field is
        // still accepted so a reconfigured client can override it.
        $payload['origin'] = $payload['origin'] ?? LocalPrintLog::ORIGIN_STANDALONE;

        try {
            $log = $service->start($payload, $request);
            return response()->json([
                'ok'   => true,
                'id'   => $log->id,
                'data' => $this->format($log),
            ], 201);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok' => false, 'error' => 'log_failed'], 500);
        }
    }

    /**
     * POST /api/v1/local-print-logs/{id}/complete
     *
     * Mirror of the web complete endpoint for the standalone app: update the
     * SAME row with the outcome. `ok=true` → completed, `ok=false` → failed.
     */
    public function complete(Request $request, LocalPrintLog $localPrintLog, LocalPrintLogService $service): JsonResponse
    {
        $result = $request->validate([
            'ok'          => 'required|boolean',
            'error'       => 'nullable|string',
            'response'    => 'nullable',
            'job_id'      => 'nullable|string|max:120',
            'duration_ms' => 'nullable|integer',
        ]);

        try {
            $log = $service->complete($localPrintLog, $result);
            return response()->json([
                'ok'   => true,
                'data' => $this->format($log),
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok' => false, 'error' => 'log_update_failed'], 500);
        }
    }

    /**
     * Normalised, API-safe representation of a log row. Keeps the response
     * stable even if the DB adds new columns, and lifts a few derived labels
     * (origin, source, status) so the client does not have to hard-code them.
     */
    protected function format(LocalPrintLog $log, bool $full = false): array
    {
        $out = [
            'id'             => $log->id,
            'origin'         => $log->origin,
            'origin_label'   => $log->originLabel(),
            'source'         => $log->source,
            'source_label'   => $log->sourceLabel(),
            'source_url'     => $log->source_url,
            'status'         => $log->status,
            'print_type'     => $log->print_type,

            'store' => $log->store ? [
                'id'   => $log->store->id,
                'name' => $log->store->store_name ?? null,
                'code' => $log->store->store_code ?? null,
            ] : null,
            'user'  => $log->user ? [
                'id'    => $log->user->id,
                'name'  => $log->user->name,
                'email' => $log->user->email,
            ] : null,

            'printer' => [
                'name'   => $log->printer_name,
                'id'     => $log->printer_id,
                'model'  => $log->printer_model,
                'tray'   => $log->tray_key,
                'tray_label' => $log->tray_label,
            ],
            'media' => [
                'paper_source'     => $log->paper_source,
                'paper_size'       => $log->paper_size,
                'media_size'       => $log->media_size,
                'orientation'      => $log->orientation,
                'color_mode'       => $log->color_mode,
                'duplex'           => $log->duplex,
                'scale_mode'       => $log->scale_mode,
                'scale_percentage' => $log->scale_percentage,
                'copies'           => $log->copies,
            ],
            'file' => [
                'url'   => $log->file_url,
                'name'  => $log->file_name,
                'type'  => $log->file_type,
                'size'  => $log->file_size,
                'pages' => $log->page_count,
                'dimensions' => $log->page_dimensions,
            ],
            'client' => [
                'ip'               => $log->ip_address,
                'user_agent'       => $log->user_agent,
                'browser'          => $log->browser,
                'operating_system' => $log->operating_system,
                'device_name'      => $log->device_name,
                'geo' => [
                    'city'         => $log->geo_city,
                    'region'       => $log->geo_region,
                    'country'      => $log->geo_country,
                    'country_code' => $log->geo_country_code,
                ],
            ],
            'timing' => [
                'requested_at' => optional($log->requested_at)->toIso8601String(),
                'started_at'   => optional($log->started_at)->toIso8601String(),
                'completed_at' => optional($log->completed_at)->toIso8601String(),
                'failed_at'    => optional($log->failed_at)->toIso8601String(),
                'duration_ms'  => $log->duration_ms,
            ],
            'linkage' => [
                'order_id'    => $log->order_id,
                'design_id'   => $log->design_id,
                'template_id' => $log->template_id,
                'product_id'  => $log->product_id,
            ],
            'printtrays_job_id' => $log->printtrays_job_id,
            'error_message'     => $log->error_message,
            'created_at'        => optional($log->created_at)->toIso8601String(),
            'updated_at'        => optional($log->updated_at)->toIso8601String(),
        ];

        // Full payload snapshots only on the detail endpoint — list responses
        // stay compact (and don't leak large JSON blobs to the client).
        if ($full) {
            $out['payloads'] = [
                'request'          => $log->request_payload,
                'response'         => $log->response_payload,
                'printer_settings' => $log->printer_settings,
                'design_settings'  => $log->design_settings,
                'system'           => $log->system_information,
            ];
        }

        return $out;
    }
}
