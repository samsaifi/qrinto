{{-- Local-print editor palette (own-design sizes + business card only; included by
     quick-flow-pc.customize when $lpUi is set). One accent, #1F6B2E, replaces the
     shared editor's sky (#0ea5e9), brand-500 (#6FB63A), gray-600 and purple
     accents for text, backgrounds and borders - including hover states and the
     elements the editor script adds later (layers list, drawers, banners).
     Pale tints of those colours become the matching pale green so text on them
     stays readable. The shared partials are untouched, so the product editor
     keeps its colours. --}}
<style>
    /* Accent backgrounds */
    .bg-brand-500, .bg-brand-600, .bg-gray-600, .bg-purple-500, .bg-purple-600, .bg-purple-700,
    .hover\:bg-brand-500:hover, .hover\:bg-brand-600:hover, .hover\:bg-brand-700:hover, .hover\:bg-brand-hover:hover,
    .hover\:bg-gray-700:hover, .hover\:bg-purple-600:hover, .hover\:bg-purple-700:hover,
    .group:hover .group-hover\:bg-brand-500, .group:hover .group-hover\:bg-brand-600, .group:hover .group-hover\:bg-brand-hover,
    .group:hover .group-hover\:bg-gray-600, .group:hover .group-hover\:bg-purple-600,
    .template-cat-chip.active {
        background-color: #1F6B2E !important;
    }
    .bg-purple-500\/20 { background-color: rgba(31, 107, 46, .2) !important }
    .bg-purple-950, .bg-purple-950\/95 { background-color: rgba(20, 39, 10, .95) !important }

    /* Accent text (icons inherit via currentColor) */
    .text-brand-500, .text-brand-600, .text-sky-500, .text-purple-600, .text-purple-700,
    .w-7.h-7.text-gray-600, .w-7.h-7 .text-gray-600, .text-purple-500,
    #shape-border-show-checkbox, #shape-border-width-val,
    .group:hover .group-hover\:text-brand-600, .group:hover .group-hover\:text-brand-hover, .group:hover .group-hover\:text-purple-700,
    .hover\:text-brand-600:hover, .hover\:text-brand-hover:hover,
    .template-cat-chip {
        color: #1F6B2E !important;
    }
    .template-cat-chip.active { color: #FFFFFF !important }
    /* Text on the (now green) accent buttons stays legible. */
    .bg-brand-500.text-slate-950, .bg-brand-500 .text-slate-950 { color: #FFFFFF !important }

    /* Accent borders */
    .border-brand-500, .border-purple-700, .border-purple-700\/80, .template-cat-chip.active,
    .group:hover .group-hover\:border-brand-300 {
        border-color: #1F6B2E !important;
    }
    .border-purple-200, .border-brand-100 { border-color: #C7DCC4 !important }

    /* Pale tints → pale green */
    .bg-purple-50, .bg-brand-50, .hover\:bg-purple-100:hover { background-color: #F0F7EF !important }
    .bg-purple-100, .bg-brand-100, .w-7.h-7.bg-gray-100 { background-color: #E3F0E1 !important }
    .text-purple-100, .text-purple-400 { color: #C7DCC4 !important }

    /* Mobile bottom toolbar: its Text tool is inline purple. */
    #mobile-customize-toolbar [stroke="#9333ea"] { stroke: #1F6B2E !important }
    #mobile-customize-toolbar span[style*="#9333ea"] { color: #1F6B2E !important }
    #mobile-customize-toolbar div[style*="#faf5ff"] { background: #F0F7EF !important; border-color: #C7DCC4 !important }
</style>
