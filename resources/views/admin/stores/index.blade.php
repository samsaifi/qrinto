@extends('layouts.admin')
@section('title', 'Store Management')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .dataTables_wrapper .dataTables_filter { display: none; }
    .dataTables_wrapper .dataTables_length { display: none; }
    table.dataTable thead th { border-bottom: none !important; }
    table.dataTable { border-collapse: collapse !important; }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.35rem 0.75rem !important;
        border-radius: 0.75rem !important;
        border: none !important;
        font-size: 0.8rem;
        margin: 0 2px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, var(--brand-600, #4f46e5), var(--brand-700, #4338ca)) !important;
        color: white !important;
        border: none !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f1f5f9 !important;
        color: #334155 !important;
        border: none !important;
    }
    .dataTables_wrapper .dataTables_info { font-size: 0.8rem; color: #64748b; padding: 1rem 0; }
    .status-toggle { transition: all 0.3s ease; }
    .store-search-input:focus { box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
</style>
@endpush

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="font-display font-bold text-2xl text-surface-900">Store Management</h1>
        <p class="text-sm text-surface-500">Manage printing shops and printer configurations</p>
    </div>
    <a href="{{ route('admin.stores.create') }}"
       class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
        <i data-lucide="plus" class="w-4 h-4"></i> Add New Store
    </a>
</div>

<!-- Filters -->
<div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5 mb-6">
    <div class="grid sm:grid-cols-4 gap-4">
        <div>
            <label class="block text-xs font-semibold text-surface-500 uppercase tracking-wider mb-1.5">Search</label>
            <input type="text" id="globalSearch" placeholder="Search stores..."
                   class="store-search-input w-full rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500 pl-9">
        </div>
        <div>
            <label class="block text-xs font-semibold text-surface-500 uppercase tracking-wider mb-1.5">City</label>
            <select id="filterCity" class="w-full rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="">All Cities</option>
                @foreach($cities as $city)
                <option value="{{ $city }}">{{ $city }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-surface-500 uppercase tracking-wider mb-1.5">Printer Type</label>
            <select id="filterPrinterType" class="w-full rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="">All Types</option>
                <option value="thermal">Thermal</option>
                <option value="inkjet">Inkjet</option>
                <option value="laser">Laser</option>
                <option value="dot_matrix">Dot Matrix</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-surface-500 uppercase tracking-wider mb-1.5">Status</label>
            <select id="filterStatus" class="w-full rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="">All Statuses</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
    </div>
</div>

<!-- DataTable -->
<div class="bg-white rounded-2xl border border-surface-100 shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table id="storesTable" class="w-full min-w-[800px]">
            <thead>
                <tr class="bg-surface-50 border-b border-surface-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider">Store</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider">Code</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider hidden lg:table-cell">Owner</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider hidden md:table-cell">Phone</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider">City</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider hidden lg:table-cell">Printer IP</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-surface-500 uppercase tracking-wider w-24">Orders</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-surface-500 uppercase tracking-wider w-24">Status</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-surface-500 uppercase tracking-wider w-40">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-50"></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
$(document).ready(function() {
    const table = $('#storesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.stores.index") }}',
            data: function(d) {
                d.filter_city = $('#filterCity').val();
                d.filter_printer_type = $('#filterPrinterType').val();
                d.filter_status = $('#filterStatus').val();
            }
        },
        columns: [
            {
                data: 'store_name',
                render: function(data, type, row) {
                    return `<div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center flex-shrink-0">
                            <span class="text-white text-xs font-bold">${data.charAt(0).toUpperCase()}</span>
                        </div>
                        <span class="font-semibold text-sm text-surface-800">${data}</span>
                    </div>`;
                }
            },
            {
                data: 'store_code',
                render: function(data) {
                    return `<span class="px-2 py-0.5 bg-surface-100 text-surface-600 text-xs font-mono rounded-lg">${data}</span>`;
                }
            },
            {
                data: 'owner_name',
                className: 'hidden lg:table-cell',
                render: function(data) {
                    return `<span class="text-sm text-surface-600">${data || '—'}</span>`;
                }
            },
            {
                data: 'phone',
                className: 'hidden md:table-cell',
                render: function(data) {
                    return `<span class="text-sm text-surface-600">${data || '—'}</span>`;
                }
            },
            {
                data: 'city',
                render: function(data) {
                    return `<span class="text-sm text-surface-600">${data || '—'}</span>`;
                }
            },
            {
                data: 'printer_ip_address',
                className: 'hidden lg:table-cell',
                render: function(data) {
                    return data
                        ? `<span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 text-xs font-mono rounded-lg">${data}</span>`
                        : '<span class="text-surface-400 text-xs">Not set</span>';
                }
            },
            {
                data: 'orders_count',
                className: 'text-center',
                orderable: true,
                render: function(data) {
                    return `<span class="px-2 py-1 bg-surface-100 text-surface-700 text-xs font-bold rounded-lg">${data || 0}</span>`;
                }
            },
            {
                data: 'is_active',
                className: 'text-center',
                orderable: true,
                render: function(data, type, row) {
                    const active = data ? true : false;
                    return `<button onclick="toggleStatus(${row.id}, this)"
                        class="status-toggle inline-flex px-3 py-1 text-xs font-bold rounded-lg cursor-pointer ${active ? 'bg-accent-100 text-accent-700 hover:bg-accent-200' : 'bg-red-100 text-red-700 hover:bg-red-200'}">
                        ${active ? 'Active' : 'Inactive'}
                    </button>`;
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    const baseUrl = '{{ url("admin/stores") }}';
                    return `<div class="flex items-center justify-center gap-1">
                        <button onclick="showQrCode('${row.store_code}', '${row.store_name.replace(/'/g, "\\'")}')"
                            class="inline-flex p-1.5 rounded-lg hover:bg-brand-50 text-surface-400 hover:text-brand-600 transition" title="Show QR Code">
                            <i data-lucide="qr-code" class="w-4 h-4"></i>
                        </button>
                        <a href="${baseUrl}/${row.id}/edit" class="inline-flex p-1.5 rounded-lg hover:bg-brand-50 text-surface-400 hover:text-brand-600 transition" title="Edit">
                            <i data-lucide="pencil" class="w-4 h-4"></i>
                        </a>
                        <button onclick="deleteStore(${row.id})" class="inline-flex p-1.5 rounded-lg hover:bg-red-50 text-surface-400 hover:text-red-600 transition" title="Delete">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>`;
                }
            }
        ],
        order: [[0, 'asc']],
        pageLength: 15,
        language: {
            emptyTable: '<div class="py-8 text-center text-surface-400"><i data-lucide="store" class="w-8 h-8 mx-auto mb-2 opacity-50"></i><p>No stores found</p></div>',
            zeroRecords: '<div class="py-8 text-center text-surface-400"><p>No matching stores</p></div>',
        },
        drawCallback: function() {
            lucide.createIcons();
        }
    });

    // Search
    let searchTimeout;
    $('#globalSearch').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => table.search(this.value).draw(), 300);
    });

    // Filters
    $('#filterCity, #filterPrinterType, #filterStatus').on('change', function() {
        table.ajax.reload();
    });

    // Toggle status
    const storeBaseUrl = '{{ url("admin/stores") }}';
    window.toggleStatus = function(id, btn) {
        fetch(`${storeBaseUrl}/${id}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                table.ajax.reload(null, false);
                Swal.fire({
                    toast: true, position: 'top-end', icon: 'success',
                    title: data.message, showConfirmButton: false, timer: 2000,
                });
            }
        })
        .catch(() => {
            Swal.fire({ icon: 'error', title: 'Oops!', text: 'Failed to toggle status.' });
        });
    };

    // Delete store
    window.deleteStore = function(id) {
        Swal.fire({
            title: 'Delete Store?',
            text: 'This store will be soft-deleted and can be restored later.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
        }).then(result => {
            if (result.isConfirmed) {
                fetch(`${storeBaseUrl}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        table.ajax.reload(null, false);
                        Swal.fire({
                            toast: true, position: 'top-end', icon: 'success',
                            title: data.message, showConfirmButton: false, timer: 2000,
                        });
                    }
                })
                .catch(() => {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to delete store.' });
                });
            }
        });
    };

    // Show QR Code modal
    const scanBaseUrl = '{{ url("store") }}';
    const qrBaseUrl = '{{ url("store") }}';
    window.showQrCode = function(storeCode, storeName) {
        const scanUrl = `${scanBaseUrl}/${storeCode}`;
        const qrPageUrl = `${qrBaseUrl}/${storeCode}/qr`;

        Swal.fire({
            title: '',
            html: `
                <div style="text-align:center;">
                    <div style="display:inline-flex;align-items:center;gap:6px;padding:6px 16px;background:linear-gradient(135deg,#e11d73,#be185d);border-radius:999px;color:white;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:16px;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9z"/><path d="M3 9V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4"/></svg>
                        Store QR Code
                    </div>
                    <h2 style="font-family:'Outfit',sans-serif;font-size:1.5rem;font-weight:800;color:#0f172a;margin-bottom:4px;">${storeName}</h2>
                    <p style="font-family:monospace;font-size:0.8rem;color:#94a3b8;margin-bottom:20px;">Code: ${storeCode}</p>
                    <div id="swal-qr-container" style="background:#f8fafc;border-radius:1rem;padding:1.25rem;display:inline-block;margin-bottom:16px;border:1px solid #e2e8f0;"></div>
                    <p style="font-size:0.9rem;font-weight:600;color:#334155;margin-bottom:4px;">📱 Scan to Start Ordering</p>
                    <p style="font-size:0.75rem;color:#94a3b8;margin-bottom:12px;">Customers scan this code in-store to browse & order</p>
                    <div style="font-family:monospace;font-size:0.7rem;color:#94a3b8;background:#f1f5f9;padding:6px 12px;border-radius:8px;display:inline-block;word-break:break-all;">${scanUrl}</div>
                </div>
            `,
            showConfirmButton: true,
            confirmButtonText: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg> &nbsp; Open Full QR Page',
            confirmButtonColor: '#e11d73',
            showCancelButton: true,
            cancelButtonText: 'Close',
            width: 420,
            didOpen: () => {
                const container = document.getElementById('swal-qr-container');
                new QRCode(container, {
                    text: scanUrl,
                    width: 240,
                    height: 240,
                    colorDark: '#0f172a',
                    colorLight: '#f8fafc',
                    correctLevel: QRCode.CorrectLevel.H,
                });
            }
        }).then(result => {
            if (result.isConfirmed) {
                window.open(qrPageUrl, '_blank');
            }
        });
    };
});
</script>
@endpush
