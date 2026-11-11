<?php

namespace App\Services\BusinessCard;

use Illuminate\Support\Str;

/**
 * Fixed specification for the double-sided business card product.
 *
 * These rules apply ONLY to the business card - every other own-design size
 * keeps the generic customizer behaviour (size/orientation from the URL).
 */
final class BusinessCard
{
    public const POINTS_PER_INCH = 72;

    /** Card trim size, always landscape: width is the larger side. */
    public const WIDTH_IN  = 3.5;
    public const HEIGHT_IN = 2.0;
    public const ORIENTATION = 'landscape';

    /** Default rendered side image resolution (300 DPI at trim size). */
    public const DPI = 300;

    /** Resolutions the editor can save a card at (picked in the editor toolbar). */
    public const DPIS = [300, 600];

    /** Title slugs (from /print/own-design/sizes) that open the business card editor. */
    public const TITLE_SLUGS = ['business-card', 'business-cards'];

    /** Editor canvas keys reused from the generic 2-page customizer. */
    public const SIDES = ['front' => 'frame_image', 'back' => 'sample_image'];

    /**
     * Output sheet formats. Page sizes are fixed server-side, never taken from
     * the client. `gap` = 'none' packs cards edge-to-edge, 'even' spreads the
     * leftover space equally across outer margins and internal gutters, 'fixed'
     * keeps a gutter between cards and centres the grid. `rotate` places the
     * card turned 90° (portrait slot); the artwork pixels are turned to match.
     * A fixed gutter is either `gutter_pt`, or picked on the sheet-layout page
     * from `gutters` (sixteenths of an inch, `gutter` = default) - only gutters
     * at which the full grid still fits are listed. `paper` groups formats
     * under that page's Paper choice.
     */
    public const FORMATS = [
        'seven_ten_10_up' => [
            'label'       => '7 × 10 inches — 10 per sheet',
            'sheet_name'  => '7 × 10 in',
            'paper'       => '7x10',
            'description' => 'Ten cards (2 × 5) edge to edge on a 7 × 10 in sheet - 5 straight cuts.',
            'filename'    => 'business-card-7x10-10-up.pdf',
            'width_pt'    => 504.0,   // 7 in
            'height_pt'   => 720.0,   // 10 in
            'gap'         => 'none',
            'recommended' => true,
            'summary'     => '2 × 5, edge to edge · 5 straight cuts',
        ],
        'letter_12_up' => [
            'label'       => '8.5 × 11 inches — 12 per sheet',
            'sheet_name'  => '8.5 × 11 in',
            'paper'       => 'letter',
            'description' => 'Twelve cards (4 × 3) turned sideways on 8.5 × 11 in (Letter) paper with a small gap between cards.',
            'filename'    => 'business-card-8.5x11-12-up.pdf',
            'width_pt'    => 612.0,   // 8.5 in * 72
            'height_pt'   => 792.0,   // 11 in * 72
            'gap'         => 'fixed',
            'gutters'     => [1, 2],  // 4 × 2 in + 3 gutters must fit 8.5 in: up to 1/8 in
            'gutter'      => 2,
            'rotate'      => true,
            'recommended' => true,
            'summary'     => '4 × 3, cards turned sideways',
        ],
        'letter_10_up' => [
            'label'       => '8.5 × 11 inches — 10 per sheet',
            'sheet_name'  => '8.5 × 11 in',
            'paper'       => 'letter',
            'description' => 'Ten cards (2 × 5) upright on 8.5 × 11 in (Letter) paper with a small gap between cards.',
            'filename'    => 'business-card-8.5x11-10-up.pdf',
            'width_pt'    => 612.0,   // 8.5 in * 72
            'height_pt'   => 792.0,   // 11 in * 72
            'gap'         => 'fixed',
            'gutters'     => [1, 2, 3],
            'gutter'      => 2,
            'summary'     => '2 × 5, cards upright',
        ],
        'a4_12_up' => [
            'label'       => 'A4 — 12 per sheet',
            'sheet_name'  => 'A4 (8.27 × 11.69 in)',
            'paper'       => 'a4',
            'description' => 'Twelve cards (4 × 3) turned sideways on A4 paper with a 1/16 in gap between cards.',
            'filename'    => 'business-card-a4-12-up.pdf',
            'width_pt'    => 595.44,  // 8.27 in
            'height_pt'   => 841.68,  // 11.69 in
            'gap'         => 'fixed',
            'gutters'     => [1],     // 4 × 2 in + 3 gutters must fit 8.27 in: only 1/16 in
            'gutter'      => 1,
            'rotate'      => true,
            'recommended' => true,
            'summary'     => '4 × 3, cards turned sideways',
        ],
        'a4_10_up' => [
            'label'       => 'A4 — 10 per sheet',
            'sheet_name'  => 'A4 (8.27 × 11.69 in)',
            'paper'       => 'a4',
            'description' => 'Ten cards (2 × 5) upright on A4 paper with a small gap between cards.',
            'filename'    => 'business-card-a4-10-up.pdf',
            'width_pt'    => 595.44,  // 8.27 in
            'height_pt'   => 841.68,  // 11.69 in
            'gap'         => 'fixed',
            'gutters'     => [1, 2, 3],
            'gutter'      => 2,
            'summary'     => '2 × 5, cards upright',
        ],
        'six_up' => [
            'label'     => '7 × 10 inches — 6 per sheet',
            'sheet_name'  => '7 × 10 in',
            'paper'       => '7x10',
            'description' => 'Six cards turned upright with 1/4 in gaps on a 7 × 10 in sheet - easier to cut by hand.',
            'filename'  => 'business-card-7x10-6-up.pdf',
            'width_pt'  => 504.0,   // 7 in
            'height_pt' => 720.0,   // 10 in
            'gap'       => 'fixed',
            'gutter_pt' => 18.0,    // 1/4 in
            'rotate'    => true,
            'summary'   => '3 × 2 with gaps · easier to cut by hand',
        ],
        'a4_centered' => [
            'label'     => 'A4 — Centered Grid',
            'sheet_name'  => 'A4',
            'description' => 'A centered card grid on A4 with even outer margins and gutters between cards.',
            'filename'  => 'business-card-a4-centered.pdf',
            'width_pt'  => 595.44,  // 8.27 in
            'height_pt' => 841.68,  // 11.69 in
            'gap'       => 'even',
        ],
    ];

    /** One `gutters` step: 1/16 in. */
    public const GUTTER_STEP_PT = 4.5;

    /** Sheets offered on the sheet-layout page, in Paper order (the A4 centred grid is still generated and downloadable). */
    public const PAGE_FORMATS = ['seven_ten_10_up', 'six_up', 'letter_12_up', 'letter_10_up', 'a4_12_up', 'a4_10_up'];

    /** Sheet selected on the sheet-layout page when nothing valid is restored. */
    public const DEFAULT_FORMAT = 'letter_12_up';

    /** How the printer turns the sheet for duplex: long edge (book) or short edge (notepad). */
    public const FLIPS = ['long', 'short'];

    /** Back-position fine-tune: ±3 mm in 0.1 mm steps, moves every back together. */
    public const NUDGE_MAX_MM = 3.0;

    /**
     * Bleed choices (mm): artwork runs this far past every cut line. The
     * artwork's edges are mirrored outward to make it, so nothing inside the
     * card moves. Limited per sheet to half the gap between cards and to the
     * outer margin (edge-to-edge sheets get none).
     */
    public const BLEEDS_MM = [0.5, 1.0, 1.5];
    public const DEFAULT_BLEED_MM = 1.0;

    /** Mirrored margin (px at DPI) on the side renders served to the preview: 1/8 in. */
    public const PREVIEW_BLEED_PX = 38;

    /**
     * Normalise print options from the request: unknown flip → long, nudges
     * clamped to ±3 mm and rounded to 0.1 mm, gap (sixteenths) only for a
     * $format with selectable gutters - an unlisted one falls back to its
     * default - and a bleed from BLEEDS_MM (else the default). Never trusts
     * client sizes.
     *
     * @return array{flip: string, dx: float, dy: float, test: bool, gap: ?int, bleed: float}
     */
    public static function printOptions(array $in = [], ?string $format = null): array
    {
        $mm = fn($v) => round(max(-self::NUDGE_MAX_MM, min(self::NUDGE_MAX_MM, (float) $v)), 1) + 0.0;

        $spec = $format ? self::format($format) : [];
        $gap  = null;
        if (isset($spec['gutters'])) {
            $want = (int) ($in['gap'] ?? 0);
            $gap  = in_array($want, $spec['gutters'], true) ? $want : $spec['gutter'];
        }

        return [
            'flip' => in_array($in['flip'] ?? null, self::FLIPS, true) ? $in['flip'] : 'long',
            'dx'   => $mm($in['dx'] ?? 0),
            'dy'   => $mm($in['dy'] ?? 0),
            'test' => filter_var($in['test'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'gap'  => $gap,
            'bleed' => in_array((float) ($in['bleed'] ?? 0), self::BLEEDS_MM, true) ? (float) $in['bleed'] : self::DEFAULT_BLEED_MM,
        ];
    }

    /** The format's card grid (points), for the chosen or default gutter. */
    public static function layout(string $format, ?int $gap = null): array
    {
        $spec = self::format($format);

        return CardSheetLayout::calculate(
            $spec['width_pt'],
            $spec['height_pt'],
            self::widthPt(),
            self::heightPt(),
            $spec['gap'],
            $spec['rotate'] ?? false,
            self::gutterPt($format, $gap)
        );
    }

    /** Most bleed a grid has room for (points): half the gutter, and no more than the outer margin. */
    public static function maxBleedPt(array $layout): float
    {
        $half = fn(float $g) => $g > 0 ? $g / 2 : 0.0;

        return max(0.0, min($half($layout['gutter_x']), $half($layout['gutter_y']), $layout['margin_x'], $layout['margin_y']));
    }

    /**
     * Bleed actually printed, in whole pixels of the 300 DPI side renders: the
     * requested bleed, rounded, but never past the grid's room (rounded down
     * there so neighbouring cards can't overlap). None on an alignment test.
     */
    public static function bleedPx(string $format, array $opts): int
    {
        if (!empty($opts['test'])) {
            return 0;
        }
        $want = (int) round(($opts['bleed'] ?? self::DEFAULT_BLEED_MM) / 25.4 * self::DPI);
        $room = (int) floor(self::maxBleedPt(self::layout($format, $opts['gap'] ?? null)) / self::POINTS_PER_INCH * self::DPI + 1e-6);

        return max(0, min($want, $room));
    }

    /** Fixed gutter in points: the chosen (or default) selectable gutter, else `gutter_pt`. */
    public static function gutterPt(string $format, ?int $gap = null): float
    {
        $spec = self::format($format);
        if (isset($spec['gutters'])) {
            return (in_array($gap, $spec['gutters'], true) ? $gap : $spec['gutter']) * self::GUTTER_STEP_PT;
        }

        return $spec['gutter_pt'] ?? 0.0;
    }

    /** Sixteenths of an inch as a reduced fraction: 2 → "1/8" ("1-8" with $sep = '-'). */
    public static function sixteenths(int $n, string $sep = '/'): string
    {
        $d = 16;
        while ($n > 0 && $n % 2 === 0 && $d > 1) {
            $n = intdiv($n, 2);
            $d = intdiv($d, 2);
        }

        return $d === 1 ? (string) $n : $n . $sep . $d;
    }

    /**
     * Filename for a sheet variant; the default options keep the plain filename.
     * A selectable gutter is always named (…-gap-1-8in), so every gutter is
     * its own cached file.
     */
    public static function variantFilename(string $format, array $opts): string
    {
        $base = preg_replace('/\.pdf$/', '', self::format($format)['filename']);
        $parts = [];
        if (!empty($opts['gap'])) {
            $parts[] = 'gap-' . self::sixteenths($opts['gap'], '-') . 'in';
        }
        if (self::bleedPx($format, $opts) > 0) {
            $parts[] = 'bleed-' . rtrim(rtrim(number_format($opts['bleed'], 1, '.', ''), '0'), '.') . 'mm';
        }
        if ($opts['test']) {
            $parts[] = 'alignment-test';
        }
        if ($opts['flip'] !== 'long') {
            $parts[] = 'short-edge';
        }
        if ($opts['dx'] != 0 || $opts['dy'] != 0) {
            $parts[] = sprintf('dx%+.1f-dy%+.1f', $opts['dx'], $opts['dy']);
        }

        return $base . ($parts ? '-' . implode('-', $parts) : '') . '.pdf';
    }

    public static function isBusinessCardTitle(?string $title): bool
    {
        return in_array(Str::slug(trim((string) $title)), self::TITLE_SLUGS, true);
    }

    public static function widthPt(): float
    {
        return self::WIDTH_IN * self::POINTS_PER_INCH;
    }

    public static function heightPt(): float
    {
        return self::HEIGHT_IN * self::POINTS_PER_INCH;
    }

    /** A supported save resolution, falling back to the default 300 DPI. */
    public static function dpi(mixed $dpi): int
    {
        return in_array((int) $dpi, self::DPIS, true) ? (int) $dpi : self::DPI;
    }

    /** Side image pixel size at a DPI: 1050 × 600 at 300, 2100 × 1200 at 600, 4200 × 2400 at 1200. */
    public static function imageWidthPx(int $dpi = self::DPI): int
    {
        return (int) round(self::WIDTH_IN * $dpi);
    }

    public static function imageHeightPx(int $dpi = self::DPI): int
    {
        return (int) round(self::HEIGHT_IN * $dpi);
    }

    public static function format(string $key): array
    {
        // Legacy edge_to_edge requests map to the edge-to-edge 7 × 10 sheet.
        if ($key === 'edge_to_edge') {
            $key = 'seven_ten_10_up';
        }

        if (!isset(self::FORMATS[$key])) {
            throw new \InvalidArgumentException("Unknown business card format [{$key}].");
        }

        return self::FORMATS[$key];
    }
}
