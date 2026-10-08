@extends('layouts.admin')
@section('title', 'Local Print #' . $log->id)

@section('content')
    @php
        $statusChip = [
            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'sent'      => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'printing'  => 'bg-blue-50 text-blue-700 border-blue-200',
            'sending'   => 'bg-blue-50 text-blue-700 border-blue-200',
            'pending'   => 'bg-surface-50 text-surface-600 border-surface-200',
            'failed'    => 'bg-rose-50 text-rose-700 border-rose-200',
            'cancelled' => 'bg-amber-50 text-amber-700 border-amber-200',
        ][$log->status] ?? 'bg-surface-50 text-surface-600 border-surface-200';

        // Small helper for "label / value" rows.
        $row = fn($label, $value) => ['label' => $label, 'value' => $value];

        $printInfo = [
            $row('Print ID', '#' . $log->id),
            $row('Date/Time', $log->created_at?->format('M j, Y H:i:s')),
            $row('Source', $log->sourceLabel()),
            $row('Origin', $log->originLabel()),
            $row('Source URL', $log->source_url),
            $row('Print Type', $log->print_type),
            $row('Duration', $log->duration_ms !== null ? number_format($log->duration_ms) . ' ms' : null),
            $row('Requested At', $log->requested_at?->format('M j, Y H:i:s')),
            $row('Completed At', $log->completed_at?->format('M j, Y H:i:s')),
            $row('Failed At', $log->failed_at?->format('M j, Y H:i:s')),
            $row('Job ID', $log->printtrays_job_id),
        ];

        $storeInfo = [
            $row('Store', $log->store?->store_name ?? ($log->store_id ? ('#' . $log->store_id) : 'Local (no store)')),
            $row('Store Code', $log->store?->store_code),
            $row('User', $log->user?->name),
            $row('User Email', $log->user?->email),
        ];

        $printerInfo = [
            $row('Printer', $log->printer_name),
            $row('Printer Model', $log->printer_model),
            $row('Printer ID', $log->printer_id),
            $row('Tray', $log->tray_label ?? $log->tray_key),
            $row('Paper Source', $log->paper_source),
            $row('Paper Size', $log->paper_size),
            $row('Media Size', $log->media_size),
            $row('Orientation', $log->orientation),
            $row('Color', $log->color_mode),
            $row('Duplex', $log->duplex),
            $row('Scaling', trim(($log->scale_mode ?? '') . ' ' . ($log->scale_percentage ? $log->scale_percentage . '%' : ''))),
            $row('Copies', $log->copies),
        ];

        $fileInfo = [
            $row('File', $log->file_name),
            $row('File URL', $log->file_url),
            $row('File Type', $log->file_type),
            $row('File Size', $log->file_size ? number_format($log->file_size) . ' bytes' : null),
            $row('Pages', $log->page_count),
            $row('Page Dimensions', $log->page_dimensions),
            $row('Generated PDF Path', $log->generated_pdf_path ? storage_path('app/public/' . $log->generated_pdf_path) : null),
            $row('Generated PDF URL', $log->generated_pdf_url),
            $row('Order ID', $log->order_id),
            $row('Design ID', $log->design_id),
            $row('Template ID', $log->template_id),
            $row('Product ID', $log->product_id),
        ];

        $netInfo = [
            $row('IP Address', $log->ip_address),
            $row('City', $log->geo_city),
            $row('Region', $log->geo_region),
            $row('Country', trim(($log->geo_country ?? '') . ($log->geo_country_code ? ' (' . $log->geo_country_code . ')' : ''))),
            $row('Browser', $log->browser),
            $row('OS', $log->operating_system),
            $row('Device', $log->device_name),
            $row('Session', $log->session_id),
            $row('User Agent', $log->user_agent),
        ];
    @endphp

    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <a href="{{ route('admin.local-print-logs.index') }}"
                class="inline-flex items-center gap-1 text-sm text-surface-500 hover:text-brand-600 font-medium mb-2">
                <i data-lucide="chevron-left" class="w-4 h-4"></i> Back to Local Print
            </a>
            <h1 class="font-display font-bold text-2xl text-surface-900">Local Print #{{ $log->id }}</h1>
        </div>
        <div class="flex items-center gap-2">
            @if ($log->generated_pdf_url)
                <a href="{{ $log->generated_pdf_url }}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold rounded-lg border border-brand-200 bg-brand-50 text-brand-700 hover:bg-brand-100 transition">
                    <i data-lucide="download" class="w-4 h-4"></i> Generated PDF
                </a>
            @endif
            <span class="inline-flex items-center px-3 py-1.5 text-sm font-semibold rounded-lg border {{ $statusChip }}">
                {{ ucfirst($log->status) }}
            </span>
        </div>
    </div>

    @php
        $sections = [
            ['title' => 'Print Information', 'icon' => 'info', 'rows' => $printInfo],
            ['title' => 'Store / User',       'icon' => 'store', 'rows' => $storeInfo],
            ['title' => 'Printer',            'icon' => 'printer', 'rows' => $printerInfo],
            ['title' => 'Design / File',      'icon' => 'file-text', 'rows' => $fileInfo],
            ['title' => 'Network / System',   'icon' => 'globe', 'rows' => $netInfo],
        ];
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
        @foreach ($sections as $sec)
            <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5">
                <div class="flex items-center gap-2 mb-4">
                    <i data-lucide="{{ $sec['icon'] }}" class="w-4 h-4 text-brand-500"></i>
                    <h2 class="font-semibold text-surface-900">{{ $sec['title'] }}</h2>
                </div>
                <dl class="space-y-2">
                    @foreach ($sec['rows'] as $r)
                        @if ($r['value'] !== null && $r['value'] !== '')
                            <div class="flex items-start gap-3 text-sm">
                                <dt class="w-40 flex-shrink-0 text-surface-500">{{ $r['label'] }}</dt>
                                <dd class="text-surface-800 break-all">{{ $r['value'] }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </div>
        @endforeach

        @if ($log->error_message)
            <div class="bg-rose-50 rounded-2xl border border-rose-200 shadow-card p-5">
                <div class="flex items-center gap-2 mb-3">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600"></i>
                    <h2 class="font-semibold text-rose-800">Error</h2>
                </div>
                <p class="text-sm text-rose-700 break-words">{{ $log->error_message }}</p>
            </div>
        @endif
    </div>

    {{-- ── Raw PrintTrays payloads (JSON) ── --}}
    @php
        $jsonBlocks = [
            'PrintTrays Request'  => $log->request_payload,
            'PrintTrays Response' => $log->response_payload,
            'Printer Settings'    => $log->printer_settings,
            'Design Settings'     => $log->design_settings,
            'System Information'   => $log->system_information,
        ];
    @endphp
    <div class="space-y-5">
        @foreach ($jsonBlocks as $title => $data)
            @if (!empty($data))
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="code" class="w-4 h-4 text-brand-500"></i>
                        <h2 class="font-semibold text-surface-900">{{ $title }}</h2>
                    </div>
                    <pre class="bg-surface-900 text-surface-100 text-xs rounded-xl p-4 overflow-x-auto"><code>{{ json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code></pre>
                </div>
            @endif
        @endforeach
    </div>
@endsection
