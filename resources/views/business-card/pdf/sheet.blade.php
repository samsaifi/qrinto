{{-- Business card print sheet: page 1 = fronts, page 2 = backs.
     All sizes are PDF points from BusinessCardPdfService/CardSheetLayout;
     the page box itself is set with setPaper(), cards are never scaled.
     $layout['back'][i] is where the back of front card i is printed (already
     mirrored for the duplex flip and moved by any fine-tune).
     $test = alignment test sheet: numbered card outlines with a centre cross
     on both sides instead of the artwork. --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            size: {{ $layout['sheet']['width'] }}pt {{ $layout['sheet']['height'] }}pt;
            margin: 0;
        }
        html, body {
            margin: 0;
            padding: 0;
        }
        .sheet {
            position: relative;
            width: {{ $layout['sheet']['width'] }}pt;
            height: {{ $layout['sheet']['height'] }}pt;
            overflow: hidden;
        }
        .sheet + .sheet {
            page-break-before: always;
        }
        .card {
            position: absolute;
            display: block;
            margin: 0;
            padding: 0;
            border: 0;
        }
        .test {
            position: absolute;
            border: 0.75pt solid #000;
            font-family: DejaVu Sans, sans-serif;
        }
        .test .h, .test .v {
            position: absolute;
            background: #000;
        }
        .test .h { left: 40%; width: 20%; top: 50%; height: 0.5pt; }
        .test .v { top: 30%; height: 40%; left: 50%; width: 0.5pt; }
        .test .n { position: absolute; left: 6pt; top: 5pt; font-size: 10pt; font-weight: bold; }
        .test .s { position: absolute; right: 6pt; bottom: 5pt; font-size: 7pt; }
    </style>
</head>
<body>
    @foreach (['front', 'back'] as $side)
        <div class="sheet">
            @foreach ($layout[$side] as $i => $slot)
                @php($pos = "left: {$slot['x']}pt; top: {$slot['y']}pt; width: {$slot['width']}pt; height: {$slot['height']}pt;")
                @if (!empty($test))
                    <div class="test" style="{{ $pos }}">
                        <span class="h"></span><span class="v"></span>
                        <span class="n">{{ $i + 1 }}</span>
                        <span class="s">{{ $side === 'front' ? 'Front' : 'Back' }}</span>
                    </div>
                @else
                    <img class="card" src="{{ $images[$side] }}" style="{{ $pos }}">
                @endif
            @endforeach
        </div>
    @endforeach
</body>
</html>
