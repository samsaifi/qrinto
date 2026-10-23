<!DOCTYPE html>
<html>
<head>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
            line-height: 0;
        }
        .page {
            width: <?php echo $width . $cssUnit; ?>;
            height: <?php echo $height . $cssUnit; ?>;
            position: relative;
            overflow: hidden;
            page-break-after: always;
        }
        .page:last-child {
            page-break-after: auto;
        }
        .page img {
            position: absolute;
            top: 0;
            left: 0;
            width: <?php echo $width . $cssUnit; ?>;
            height: <?php echo $height . $cssUnit; ?>;
            display: block;
        }
    </style>
</head>
<body>
    @foreach ($pages as $imagePath)
        <div class="page">
            @if ($imagePath)
                <img src="{{ $imagePath }}">
            @endif
        </div>
    @endforeach
</body>
</html>
