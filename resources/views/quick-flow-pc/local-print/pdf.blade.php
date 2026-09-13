@extends('layouts.quick-flow-pc')
@section('title', 'Upload a Media | Qrinto')

@section('content')
    <div class="w-full bg-[#fafcf9] min-h-screen py-4 px-3 md:py-10 md:px-6 lg:px-16 font-sans">
        <div class="max-w-[820px] mx-auto">

            <div class="flex items-center gap-2 mb-3 md:flex-col md:items-start md:gap-0 md:mb-0">
                <a href="{{ route('localprint.start') }}"
                    class="text-[11px] md:text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors shrink-0">
                    ← <span class="hidden md:inline">Back</span>
                </a>
                <h1 class="text-base md:text-2xl font-extrabold text-[#112419] tracking-tight md:mt-4">Upload a Media</h1>
            </div>

            @if ($errors->any())
                <div class="mt-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-medium">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('localprint.upload') }}" enctype="multipart/form-data" class="mt-4 md:mt-6"
                x-data="{ name: '', over: false }" @dragover.prevent="over = true" @dragleave.prevent="over = false"
                @drop.prevent="over = false; $refs.file.files = $event.dataTransfer.files; name = $refs.file.files[0]?.name || ''; $refs.file.files.length && $el.submit()">
                @csrf
                <label
                    class="block cursor-pointer rounded-xl md:rounded-2xl border-2 border-dashed transition-colors bg-white text-center py-10 px-4 md:py-16 md:px-6"
                    :class="over ? 'border-[#287d3c] bg-[#f2f7f2]' : 'border-slate-300'">
                    <input type="file" name="file" accept="application/pdf,image/jpeg,image/png,image/webp,image/tiff"
                        class="hidden" x-ref="file"
                        @change="name = $refs.file.files[0]?.name || ''; $refs.file.files.length && $el.closest('form').submit()">
                    <i data-lucide="upload" class="w-7 h-7 mx-auto text-slate-500"></i>
                    <p class="font-bold text-slate-800 mt-4"
                        x-text="name || 'Drop an image or PDF here, or click to choose'"></p>
                    <p class="text-xs text-slate-400 mt-1">PDF, JPEG, PNG, WebP, or TIFF - up to 200 MB</p>
                </label>
            </form>

        </div>
    </div>
@endsection
