<?php

namespace App\Services\BusinessCard;

/**
 * Shared n-up layout calculation for placing fixed-size cards on a sheet.
 *
 * All values are PDF points (1 in = 72 pt) measured from the TOP-LEFT of the
 * page, which is how the Blade/dompdf template positions elements. Cards are
 * never scaled: each slot is exactly the card size, in the card's own
 * (landscape) orientation unless the format asks for the card turned 90°
 * ($rotate), and as many complete cards as fit are placed.
 *
 * Gap modes: none (edge to edge), even (margins == gutters), fixed (a set
 * gutter between cards, the grid centred on the sheet).
 *
 * Back-side slots are listed in the same order as the front (index i = the
 * back of front card i), in back-page coordinates:
 *   back       - mirrored about the vertical centre (long-edge duplex flip)
 *   back_short - mirrored about the horizontal centre (short-edge flip)
 * With a centred, symmetric grid either mirrored set equals the front set, so
 * front and back share identical positions.
 */
final class CardSheetLayout
{
    public const GAP_NONE = 'none';
    public const GAP_EVEN = 'even';
    public const GAP_FIXED = 'fixed';

    /**
     * @return array{
     *   sheet: array{width: float, height: float},
     *   card: array{width: float, height: float},
     *   rotated: bool, cols: int, rows: int, count: int,
     *   margin_x: float, margin_y: float, gutter_x: float, gutter_y: float,
     *   front: list<array{x: float, y: float, width: float, height: float}>,
     *   back: list<array{x: float, y: float, width: float, height: float}>,
     *   back_short: list<array{x: float, y: float, width: float, height: float}>
     * }
     */
    public static function calculate(
        float $sheetW,
        float $sheetH,
        float $cardW,
        float $cardH,
        string $gap,
        bool $rotate = false,
        float $gutter = 0.0
    ): array {
        if ($sheetW <= 0 || $sheetH <= 0 || $cardW <= 0 || $cardH <= 0) {
            throw new \InvalidArgumentException('Sheet and card dimensions must be positive.');
        }
        if (!in_array($gap, [self::GAP_NONE, self::GAP_EVEN, self::GAP_FIXED], true)) {
            throw new \InvalidArgumentException("Unknown gap mode [{$gap}].");
        }
        if ($gutter < 0 || ($gap !== self::GAP_FIXED && $gutter > 0)) {
            throw new \InvalidArgumentException('A gutter is only valid for the fixed gap mode.');
        }

        // A turned card occupies a portrait slot on the sheet.
        [$w, $h] = $rotate ? [$cardH, $cardW] : [$cardW, $cardH];
        $cols = self::fit($sheetW, $w, $gutter);
        $rows = self::fit($sheetH, $h, $gutter);
        if ($cols * $rows === 0) {
            throw new \RuntimeException('The card does not fit on the sheet.');
        }

        [$marginX, $gutterX] = self::spacing($sheetW, $w, $cols, $gap, $gutter);
        [$marginY, $gutterY] = self::spacing($sheetH, $h, $rows, $gap, $gutter);

        $front = [];
        $back  = [];
        $backShort = [];
        for ($r = 0; $r < $rows; $r++) {
            for ($c = 0; $c < $cols; $c++) {
                $x = $marginX + $c * ($w + $gutterX);
                $y = $marginY + $r * ($h + $gutterY);
                $front[] = self::slot($x, $y, $w, $h);
                $back[]  = self::slot($sheetW - $x - $w, $y, $w, $h);
                $backShort[] = self::slot($x, $sheetH - $y - $h, $w, $h);
            }
        }

        return [
            'sheet'    => ['width' => $sheetW, 'height' => $sheetH],
            'card'     => ['width' => $w, 'height' => $h],
            'rotated'  => $rotate,
            'cols'     => $cols,
            'rows'     => $rows,
            'count'    => $cols * $rows,
            'margin_x' => round($marginX, 4),
            'margin_y' => round($marginY, 4),
            'gutter_x' => round($gutterX, 4),
            'gutter_y' => round($gutterY, 4),
            'front'    => $front,
            'back'     => $back,
            'back_short' => $backShort,
        ];
    }

    /** Number of complete cards along one axis (tolerates float noise, never rounds up a partial card). */
    private static function fit(float $sheet, float $card, float $gutter = 0.0): int
    {
        return (int) floor(($sheet + $gutter + 1e-6) / ($card + $gutter));
    }

    /** @return array{0: float, 1: float} [outer margin, internal gutter] */
    private static function spacing(float $sheet, float $card, int $count, string $gap, float $gutter = 0.0): array
    {
        if ($gap === self::GAP_FIXED) {
            return [max(0.0, $sheet - $count * $card - ($count - 1) * $gutter) / 2, $gutter];
        }

        $free = max(0.0, $sheet - $count * $card);

        if ($gap === self::GAP_EVEN) {
            $each = $free / ($count + 1);
            return [$each, $each];
        }

        // Edge-to-edge: no gutters; any leftover (zero for an exact fit) is split
        // so the block stays centred instead of hugging one edge.
        return [$free / 2, 0.0];
    }

    private static function slot(float $x, float $y, float $w, float $h): array
    {
        return ['x' => round($x, 4), 'y' => round($y, 4), 'width' => $w, 'height' => $h];
    }
}
