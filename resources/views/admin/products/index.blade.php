@extends('layouts.admin')
@section('title', 'Products')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="font-display font-bold text-2xl text-surface-900">Products</h1>
        <p class="text-sm text-surface-500">Manage your product catalog</p>
    </div>
    <div class="flex items-center gap-3">
        <button type="button" id="bulkDeleteBtn"
                class="hidden items-center gap-2 px-4 py-2.5 bg-red-600 text-white text-sm font-semibold rounded-xl hover:bg-red-700 transition shadow-lg shadow-red-200"
                onclick="confirmBulkDelete()">
            <i data-lucide="trash-2" class="w-4 h-4"></i>
            Delete <span id="selectedCount" class="bg-white/20 px-1.5 py-0.5 rounded text-xs ml-0.5">0</span>
        </button>
        <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Product
        </a>
    </div>
</div>

<form id="bulkDeleteForm" action="{{ route('admin.products.bulkDelete') }}" method="POST" class="hidden">@csrf</form>

<div class="bg-white rounded-2xl border border-surface-100 shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[700px]">
            <thead>
                <tr class="bg-surface-50 border-b border-surface-100">
                    <th class="w-12 pl-5 pr-1 py-3">
                        <input type="checkbox" id="selectAll" class="rounded text-brand-600 focus:ring-brand-500 cursor-pointer" onchange="toggleSelectAll(this)">
                    </th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider">Product</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider">Page type</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider">Number of pages</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider hidden md:table-cell">Category</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider w-28">Price</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-surface-500 uppercase tracking-wider w-20 hidden sm:table-cell">Status</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-surface-500 uppercase tracking-wider w-24">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-50">
                @forelse($products as $product)
                <tr class="hover:bg-surface-50/60 transition-colors product-row group">
                    <td class="w-12 pl-5 pr-1 py-3">
                        <input type="checkbox" name="product_ids[]" value="{{ $product->id }}"
                               class="product-checkbox rounded text-brand-600 focus:ring-brand-500 cursor-pointer"
                               onchange="updateSelection()">
                    </td>
                    <td class="px-3 py-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-lg overflow-hidden bg-surface-100 flex-shrink-0 ring-1 ring-surface-200/50">
                                @if($product->frame_image)
                                <img src="{{ $product->getFeaturedImageUrl($product->frame_image) }}" alt="" class="w-full h-full object-cover">
                                @else
                                <div class="w-full h-full flex items-center justify-center"><i data-lucide="image" class="w-4 h-4 text-surface-300"></i></div>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('admin.products.edit', $product) }}" class="font-semibold text-sm text-surface-800 hover:text-brand-600 transition block">{{ $product->name }}</a>
                                <p class="text-xs text-surface-400">{{ $product->sku ?: '—' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-3 hidden md:table-cell">
                        <span class="text-sm text-surface-600"> 
                            {{ $product->pdf_orientation }}
                                 
                        </span>
                    </td>
                    <td class="px-3 py-3 hidden md:table-cell">
                        <span class="text-sm text-surface-600"> 
                            @switch($product->no_of_pages)
                                @case(4)
                                    Four pages
                                    @break
                                @case(2)
                                    Double pages 
                                    @break 
                                @case(1)
                                    Single Page  
                                @break 
                            @endswitch
                        </span>
                    </td>
                    <td class="px-3 py-3 hidden md:table-cell">
                        <span class="text-sm text-surface-600">{{ $product->category->name ?? '—' }}</span>
                    </td>
                    <td class="px-3 py-3">
                        <span class="font-semibold text-sm text-surface-800">${{ number_format($product->base_price, 0) }}</span>
                        @if($product->compare_price)
                        <span class="text-[11px] text-surface-400 line-through ml-0.5">${{ number_format($product->compare_price, 0) }}</span>
                        @endif
                    </td>
                    <td class="px-3 py-3 text-center hidden sm:table-cell">
                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-md {{ $product->is_active ? 'bg-accent-100 text-accent-700' : 'bg-surface-200 text-surface-500' }}">
                            {{ $product->is_active ? 'Active' : 'Draft' }}
                        </span>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex p-1.5 rounded-lg hover:bg-brand-50 text-surface-400 hover:text-brand-600 transition" title="Edit">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ route('admin.products.mask', $product) }}" class="inline-flex p-1.5 rounded-lg hover:bg-purple-50 text-surface-400 hover:text-purple-600 transition" title="Mask Editor">
                                <i data-lucide="layers" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-surface-400">No products yet. <a href="{{ route('admin.products.create') }}" class="text-brand-600 font-medium">Add your first product</a>.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
    <div class="px-6 py-4 border-t border-surface-100">{{ $products->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function toggleSelectAll(el) {
    document.querySelectorAll('.product-checkbox').forEach(cb => {
        cb.checked = el.checked;
        cb.closest('tr').classList.toggle('bg-brand-50/40', el.checked);
    });
    updateSelection();
}

function updateSelection() {
    const all = document.querySelectorAll('.product-checkbox');
    const checked = document.querySelectorAll('.product-checkbox:checked');
    const btn = document.getElementById('bulkDeleteBtn');
    const badge = document.getElementById('selectedCount');
    const master = document.getElementById('selectAll');

    btn.classList.toggle('hidden', checked.length === 0);
    btn.classList.toggle('inline-flex', checked.length > 0);
    badge.textContent = checked.length;

    master.checked = all.length > 0 && checked.length === all.length;
    master.indeterminate = checked.length > 0 && checked.length < all.length;

    all.forEach(cb => cb.closest('tr').classList.toggle('bg-brand-50/40', cb.checked));
}

function confirmBulkDelete() {
    const checked = document.querySelectorAll('.product-checkbox:checked');
    if (!checked.length) return;
    const n = checked.length;
    if (!confirm(n === 1 ? 'Delete this product?' : `Delete ${n} products?`)) return;

    const form = document.getElementById('bulkDeleteForm');
    form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());
    checked.forEach(cb => {
        const i = document.createElement('input');
        i.type = 'hidden'; i.name = 'ids[]'; i.value = cb.value;
        form.appendChild(i);
    });
    form.submit();
}
</script>
@endpush
