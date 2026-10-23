<!DOCTYPE html>
<html>
<head>
    <style>
        @page {
            size: <?php echo $width; ?>in <?php echo $height; ?>in;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            line-height: 0;
        }
        .page {
            width: <?php echo $width; ?>in;
            height: <?php echo $height; ?>in;
            position: relative;
            overflow: hidden;
            page-break-after: always;
        }
        .page:last-child {
            page-break-after: auto;
        }
        img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: fill;
            margin: 0;
            padding: 0;
            border: none;
        }
    </style>
</head>
<body>
    @if($orientation === 'portrait')
        {{-- Page 1: frame image --}}
        <div class="page">
            @if($images['frame_image'] ?? null)<img src="{{ $images['frame_image'] }}">@endif
        </div>
        {{-- Page 2: sample image --}}
        <div class="page">
            @if($images['sample_image'] ?? null)<img src="{{ $images['sample_image'] }}">@endif
        </div>
    @else
        {{-- Page 1: frame image --}}
        <div class="page">
            @if($images['frame_image'] ?? null)<img src="{{ $images['frame_image'] }}">@endif
        </div>
        {{-- Page 2: sample image --}}
        <div class="page">
            @if($images['sample_image'] ?? null)<img src="{{ $images['sample_image'] }}">@endif
        </div>
    @endif
</body>
</html>
