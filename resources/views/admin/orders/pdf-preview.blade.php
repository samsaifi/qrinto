<!DOCTYPE html>
<html>

<head>
    <title>PDF Preview — Order {{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #1a1a2e;
            font-family: 'Segoe UI', system-ui, sans-serif;
            color: #e0e0e0;
            min-height: 100vh;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(30, 30, 50, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .toolbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .toolbar h1 {
            font-size: 16px;
            font-weight: 600;
            color: #fff;
        }

        .toolbar .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-brand {
            background: rgba(214, 95, 50, 0.15);
            color: #d65f32;
            border: 1px solid rgba(214, 95, 50, 0.3);
        }

        .badge-emerald {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .badge-amber {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .toolbar-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-outline {
            background: transparent;
            color: #94a3b8;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
        }

        .btn-primary {
            background: #6366f1;
            color: #fff;
        }

        .btn-primary:hover {
            background: #4f46e5;
        }

        .info-bar {
            background: rgba(59, 130, 246, 0.08);
            border-bottom: 1px solid rgba(59, 130, 246, 0.2);
            padding: 10px 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            font-size: 13px;
            color: #94a3b8;
        }

        .info-bar strong {
            color: #cbd5e1;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-item+.info-item::before {
            content: '·';
            margin-right: 6px;
            color: rgba(255, 255, 255, 0.2);
        }

        /* Page container */
        .pages-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
            gap: 40px;
        }

        .page-label {
            text-align: center;
            font-size: 13px;
            font-weight: 600;
            color: #0ea5e9;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }

        /* Each simulated page */
        .pdf-page {
            background: #fff;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);
            border-radius: 4px;
            overflow: hidden;
            position: relative;
        }

        /* Fold indicator line */
        .fold-line {
            position: absolute;
            z-index: 10;
            pointer-events: none;
        }

        .fold-line {
            left: 0;
            right: 0;
            top: 50%;
            height: 0;
            border-top: 2px dashed rgba(255, 0, 0, 0.35);
        }

        .fold-line::after {
            content: 'FOLD LINE';
            position: absolute;
            top: -10px;
            right: 8px;
            font-size: 9px;
            font-weight: 700;
            color: rgba(255, 0, 0, 0.5);
            letter-spacing: 1px;
        }

        /* Image containers inside pages */
        /* Always Top/Bottom Fold */
        .img-container {
            width: 100%;
            height: 50%;
            position: absolute;
            left: 0;
            overflow: hidden;
        }

        .img-container.top {
            top: 0;
        }

        .img-container.bottom {
            bottom: 0;
        }

        .img-container img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: fill;
        }

        .img-container .img-label {
            position: absolute;
            bottom: 4px;
            left: 4px;
            z-index: 20;
            background: rgba(0, 0, 0, 0.6);
            color: #fff;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 4px;
            pointer-events: none;
        }

        .no-image {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background: #f1f5f9;
            color: #94a3b8;
            font-size: 13px;
            font-weight: 600;
        }

        /* Image listing below */
        .image-debug {
            max-width: 900px;
            margin: 0 auto 40px;
            padding: 20px;
        }

        .image-debug h3 {
            color: #94a3b8;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 16px;
        }

        .image-debug table {
            width: 100%;
            border-collapse: collapse;
        }

        .image-debug th,
        .image-debug td {
            text-align: left;
            padding: 10px 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
            font-size: 13px;
        }

        .image-debug th {
            color: #0ea5e9;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }

        .image-debug td {
            color: #cbd5e1;
        }

        .image-debug code {
            background: rgba(255, 255, 255, 0.06);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-family: 'Cascadia Code', 'Fira Code', monospace;
            color: #a78bfa;
        }

        .image-debug .thumb {
            width: 48px;
            height: 48px;
            border-radius: 6px;
            object-fit: cover;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>

<body>
    <!-- Toolbar -->
    <div class="toolbar">
        <div class="toolbar-left">
            <h1>📄 PDF Design Preview</h1>
            <span class="badge badge-brand">{{ $order->order_number }}</span>
            <span class="badge badge-emerald">{{ $orientation }} fold</span>
            <span class="badge badge-amber">{{ $width }}" × {{ $height }}"</span>
        </div>
        <div class="toolbar-right">
            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline">← Back to Order</a>
            @if ($item && $item->pdf_path)
                <a href="{{ asset('storage/' . $item->pdf_path) }}" target="_blank" class="btn btn-primary">View Actual
                    PDF</a>
            @endif
        </div>
    </div>

    <!-- Info bar -->
    <div class="info-bar">
        <div class="info-item"><strong>Paper Size:</strong> {{ $width }}" × {{ $height }}"</div>
        <div class="info-item"><strong>Original:</strong> {{ $flowData['size_width'] ?? '?' }}" ×
            {{ $flowData['size_height'] ?? '?' }}"</div>
        <div class="info-item"><strong>Orientation:</strong> {{ $orientation }}</div>
        <div class="info-item"><strong>Product:</strong> {{ $item->product_name ?? 'N/A' }}</div>
        @if (isset($flowData['size_dimensions']))
            <div class="info-item"><strong>Size Label:</strong> {{ $flowData['size_dimensions'] }}</div>
        @endif
    </div>

    <div class="pages-wrapper">
        @php
            // Calculate pixel display size: 1 inch = 96px for browser preview
            $scale = 96;
            $displayWidth = $width * $scale;
            $displayHeight = $height * $scale;
        @endphp

        @if ($orientation === 'portrait')
            <!-- Portrait Grouping -->
            <!-- Page 1 -->
            <div>
                <div class="page-label">Page 1</div>
                <div class="pdf-page" style="width: {{ $displayWidth }}px; height: {{ $displayHeight }}px;">
                    <div class="fold-line"></div>
                    <div class="img-container top {{ $rotations['frame_image'] ?? 'rotate_0' }}">
                        <span class="img-label">frame_image ({{ $rotations['frame_image'] ?? 'rotate_0' }})</span>
                        @if (!empty($images['frame_image']))
                            <img src="{{ $images['frame_image'] }}" alt="Frame Image">
                        @else
                            <div class="no-image">No frame_image</div>
                        @endif
                    </div>
                    <div class="img-container bottom {{ $rotations['overlay_image'] ?? 'rotate_0' }}">
                        <span class="img-label">overlay_image ({{ $rotations['overlay_image'] ?? 'rotate_0' }})</span>
                        @if (!empty($images['overlay_image']))
                            <img src="{{ $images['overlay_image'] }}" alt="Overlay Image">
                        @else
                            <div class="no-image">No overlay_image</div>
                        @endif
                    </div>

                </div>
            </div>

            <!-- Page 2 -->
            <div>
                <div class="page-label">Page 2</div>
                <div class="pdf-page" style="width: {{ $displayWidth }}px; height: {{ $displayHeight }}px;">
                    <div class="fold-line"></div>
                    <div class="img-container top  {{ $rotations['sample_image'] ?? 'rotate_0' }}">
                        <span class="img-label">sample_image ({{ $rotations['sample_image'] ?? 'rotate_0' }})</span>
                        @if (!empty($images['sample_image']))
                            <img src="{{ $images['sample_image'] }}" alt="Sample Image">
                        @else
                            <div class="no-image">No sample_image</div>
                        @endif
                    </div>
                    <div class="img-container  bottom {{ $rotations['background_image'] ?? 'rotate_0' }}">
                        <span class="img-label">background_image
                            ({{ $rotations['background_image'] ?? 'rotate_0' }})</span>
                        @if (!empty($images['background_image']))
                            <img src="{{ $images['background_image'] }}" alt="Background Image">
                        @else
                            <div class="no-image">No background_image</div>
                        @endif
                    </div>

                </div>
            </div>
        @else
            <!-- Landscape Grouping -->
            <!-- Page 1 -->
            <div>
                <div class="page-label">Page 1</div>
                <div class="pdf-page" style="width: {{ $displayWidth }}px; height: {{ $displayHeight }}px;">
                    <div class="fold-line"></div>
                    <div class="img-container top {{ $rotations['sample_image'] ?? 'rotate_0' }}">
                        <span class="img-label">sample_image ({{ $rotations['sample_image'] ?? 'rotate_0' }})</span>
                        @if (!empty($images['sample_image']))
                            <img src="{{ $images['sample_image'] }}" alt="Sample Image">
                        @else
                            <div class="no-image">No sample_image</div>
                        @endif
                    </div>
                    <div class="img-container bottom {{ $rotations['background_image'] ?? 'rotate_0' }}">
                        <span class="img-label">background_image
                            ({{ $rotations['background_image'] ?? 'rotate_0' }})</span>
                        @if (!empty($images['background_image']))
                            <img src="{{ $images['background_image'] }}" alt="Background Image">
                        @else
                            <div class="no-image">No background_image</div>
                        @endif
                    </div>

                </div>
            </div>

            <!-- Page 2 -->
            <div>
                <div class="page-label">Page 2</div>
                <div class="pdf-page" style="width: {{ $displayWidth }}px; height: {{ $displayHeight }}px;">
                    <div class="fold-line"></div>
                    <div class="img-container top {{ $rotations['frame_image'] ?? 'rotate_0' }}">
                        <span class="img-label">frame_image ({{ $rotations['frame_image'] ?? 'rotate_0' }})</span>
                        @if (!empty($images['frame_image']))
                            <img src="{{ $images['frame_image'] }}" alt="Frame Image">
                        @else
                            <div class="no-image">No frame_image</div>
                        @endif
                    </div>
                    <div class="img-container bottom {{ $rotations['overlay_image'] ?? 'rotate_0' }}">
                        <span class="img-label">overlay_image ({{ $rotations['overlay_image'] ?? 'rotate_0' }})</span>
                        @if (!empty($images['overlay_image']))
                            <img src="{{ $images['overlay_image'] }}" alt="Overlay Image">
                        @else
                            <div class="no-image">No overlay_image</div>
                        @endif
                    </div>

                </div>
            </div>
        @endif
    </div>

    <!-- Debug info -->
    <div class="image-debug">
        <h3>🔍 Image Source Details</h3>
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th>Slot</th>
                    <th>Source Path</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach (['frame_image', 'sample_image', 'background_image', 'overlay_image'] as $key)
                    <tr>
                        <td>
                            @if (!empty($images[$key]))
                                <img src="{{ $images[$key] }}" class="thumb" alt="{{ $key }}">
                            @else
                                <div class="thumb"
                                    style="background:#1e293b;display:flex;align-items:center;justify-content:center;font-size:10px;color:#475569;">
                                    —</div>
                            @endif
                        </td>
                        <td><code>{{ $key }}</code></td>
                        <td><code>{{ $images[$key] ?? '(empty)' }}</code></td>
                        <td>
                            @if (!empty($images[$key]))
                                <span style="color:#0ea5e9;">✓ loaded</span>
                            @else
                                <span style="color:#ef4444;">✗ missing</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
