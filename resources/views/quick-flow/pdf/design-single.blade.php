<!DOCTYPE html>
<html>
<head>
    <style>
        @page {
            size: <?php echo $width . $cssUnit; ?> <?php echo $height . $cssUnit; ?>;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        .page {
            width: <?php echo $width . $cssUnit; ?>;
            height: <?php echo $height . $cssUnit; ?>;
            position: relative;
            overflow: hidden;
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
    <div class="page">
        @if($image)
            <img src="{{ $image }}">
        @endif
    </div>
</body>
</html>
