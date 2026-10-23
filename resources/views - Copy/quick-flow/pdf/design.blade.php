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
            box-sizing: border-box;
        }

        .page {
            width: <?php echo $width; ?>in;
            height: <?php echo $height; ?>in;
            position: relative;
            page-break-after: always;
            overflow: hidden;
        }

        /* Always Top/Bottom Fold */
        .img-container {
            width: <?php echo $width; ?>in;
            height: <?php echo $height / 2; ?>in;
            position: absolute;
            left: 0;
            overflow: hidden;
        }

        .img-container.top {
            top: 0;
        }

        .img-container.bottom {
            top: <?php echo $height / 2; ?>in;
        }

        img {
            width: <?php echo $width; ?>in;
            height: <?php echo $height / 2; ?>in;
            max-width: <?php echo $width; ?>in;
            max-height: <?php echo $height / 2; ?>in;
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
    <!-- Portrait Grouping -->
    <!-- Page 1 -->
    <div class="page">
        <div class="img-container top {{ $rotations['frame_image'] ?? 'rotate_0' }}">
            @if($images['frame_image'])<img src="{{ $images['frame_image'] }}">@endif
        </div>
        <div class="img-container bottom {{ $rotations['overlay_image'] ?? 'rotate_0' }}">
            @if($images['overlay_image'])<img src="{{ $images['overlay_image'] }}">@endif
        </div>
    </div>

    <!-- Page 2 -->
    <div class="page" style="page-break-after: avoid;">
        <div class="img-container top  {{ $rotations['sample_image'] ?? 'rotate_0' }}">
            @if($images['sample_image'])<img src="{{ $images['sample_image'] }}">@endif
        </div>
        <div class="img-container bottom  {{ $rotations['background_image'] ?? 'rotate_0' }}">
            @if($images['background_image'])<img src="{{ $images['background_image'] }}">@endif
        </div>

    </div>
    @else
    <!-- Landscape Grouping -->
    <!-- Page 1 -->
    <div class="page">
        <div class="img-container top {{ $rotations['sample_image'] ?? 'rotate_0' }}">
            @if($images['sample_image'])<img src="{{ $images['sample_image'] }}">@endif
        </div>
        <div class="img-container bottom {{ $rotations['background_image'] ?? 'rotate_0' }}">
            @if($images['background_image'])<img src="{{ $images['background_image'] }}">@endif
        </div>

    </div>

    <!-- Page 2 -->
    <div class="page" style="page-break-after: avoid;">
        <div class="img-container top {{ $rotations['frame_image'] ?? 'rotate_180_plus' }}">
            @if($images['frame_image'])<img src="{{ $images['frame_image'] }}">@endif
        </div>
        <div class="img-container bottom {{ $rotations['overlay_image'] ?? 'rotate_0' }}">
            @if($images['overlay_image'])<img src="{{ $images['overlay_image'] }}">@endif
        </div>
    </div>
    @endif
</body>

</html>