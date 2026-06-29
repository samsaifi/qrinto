<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Thumbnails Gallery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
        }
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .thumb-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .thumb-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>
<body class="p-8">
    <div class="max-w-7xl mx-auto">
        <header class="mb-12 text-center">
            <h1 class="text-4xl font-extrabold text-slate-900 mb-4 tracking-tight">Product Asset Thumbnails</h1>
            <p class="text-slate-500 max-w-2xl mx-auto">
                Automatically generated thumbnails for all product images found in the storage directory.
                Found <span class="font-bold text-indigo-600">{{ count($thumbnails) }}</span> items.
            </p>
        </header>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
            @foreach($thumbnails as $item)
            <div class="thumb-card bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-sm group">
                <div class="aspect-square bg-slate-50 relative overflow-hidden">
                    <img 
                        src="{{ $item['thumbnail_url'] }}" 
                        alt="{{ $item['name'] }}"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                        onerror="this.src='https://placehold.co/300x300?text=Error'"
                    >
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                        <a href="{{ $item['original_url'] }}" target="_blank" class="p-2 bg-white rounded-full text-slate-900 hover:bg-indigo-600 hover:text-white transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/>
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="p-4 bg-white">
                    <p class="text-xs font-bold text-slate-900 truncate mb-1">{{ $item['name'] }}</p>
                    <p class="text-[10px] text-slate-400 truncate uppercase tracking-wider font-semibold">
                        {{ dirname($item['path']) }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>

        @if(count($thumbnails) === 0)
        <div class="text-center py-24 bg-white rounded-[2.5rem] border-2 border-dashed border-slate-200">
            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">No Images Found</h3>
            <p class="text-slate-500">We couldn't find any image files in the products directory.</p>
        </div>
        @endif
    </div>

    <footer class="mt-20 mb-12 text-center border-t border-slate-100 pt-8">
        <p class="text-sm text-slate-400">© {{ date('Y') }} Qrinto Digital Assets Management</p>
    </footer>
</body>
</html>
