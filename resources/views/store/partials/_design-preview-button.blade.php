{{--
    Eye button that opens the shared design-preview modal (store/orders-v2).

    Expects from queue-row-v2: $order, $previewImages (page-ordered URLs),
    $previewOrientation ('auto' = decide from the page-1 image).

    The preview markup sits in a <template> (images aren't loaded until the
    modal clones it in), so rows stay light.
--}}
@if (!empty($previewImages))
    <button type="button" class="v2-btn-eye" title="Preview design" aria-label="Preview design"
        data-design-preview="design-preview-{{ $order->id }}" data-order-number="{{ $order->order_number }}">
        <i data-lucide="eye" class="w-4 h-4"></i>
    </button>

    <template id="design-preview-{{ $order->id }}">
        @include('quick-flow-pc.local-print.partials.book-preview', [
            'images' => $previewImages,
            'orientation' => $previewOrientation ?? 'portrait',
        ])
    </template>
@endif
