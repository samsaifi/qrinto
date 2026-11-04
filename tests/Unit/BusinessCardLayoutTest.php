<?php

namespace Tests\Unit;

use App\Services\BusinessCard\BusinessCard;
use App\Services\BusinessCard\CardSheetLayout;
use PHPUnit\Framework\TestCase;

class BusinessCardLayoutTest extends TestCase
{
    public function test_card_spec_is_landscape_3_5_by_2_inches(): void
    {
        $this->assertSame(252.0, BusinessCard::widthPt());
        $this->assertSame(144.0, BusinessCard::heightPt());
        $this->assertGreaterThan(BusinessCard::HEIGHT_IN, BusinessCard::WIDTH_IN);
        $this->assertSame('landscape', BusinessCard::ORIENTATION);
        $this->assertSame([1050, 600], [BusinessCard::imageWidthPx(), BusinessCard::imageHeightPx()]);
    }

    public function test_sheet_formats_have_exact_point_sizes(): void
    {
        $this->assertSame([612.0, 792.0], [BusinessCard::FORMATS['letter_10_up']['width_pt'], BusinessCard::FORMATS['letter_10_up']['height_pt']]);
        $this->assertEqualsWithDelta(8.27 * 72, BusinessCard::FORMATS['a4_centered']['width_pt'], 1e-9);
        $this->assertEqualsWithDelta(11.69 * 72, BusinessCard::FORMATS['a4_centered']['height_pt'], 1e-9);
    }

    public function test_business_card_title_detection_is_specific(): void
    {
        foreach (['business-card', ' business-card', 'Business Card', 'business-cards'] as $title) {
            $this->assertTrue(BusinessCard::isBusinessCardTitle($title), $title);
        }
        foreach ([null, '', 'flat', 'folded', 'flat-double', 'custom-flat', 'business'] as $title) {
            $this->assertFalse(BusinessCard::isBusinessCardTitle($title), (string) $title);
        }
    }

    public function test_8_5x11_letter_10_up_has_10_cards_with_equal_borders_and_gaps(): void
    {
        // 8.5 × 11 in (612 × 792 pt), 3.5 × 2 in (252 × 144 pt) cards, even gaps
        $l = CardSheetLayout::calculate(612, 792, 252, 144, CardSheetLayout::GAP_EVEN);

        $this->assertSame([2, 5, 10], [$l['cols'], $l['rows'], $l['count']]);
        // Horizontal: (8.5 - 2*3.5)/3 = 0.5 in = 36 pt (margins == gutter)
        $this->assertSame([36.0, 36.0], [$l['margin_x'], $l['gutter_x']]);
        // Vertical: (11 - 5*2)/6 = 1/6 in = 12 pt (margins == gutter)
        $this->assertSame([12.0, 12.0], [$l['margin_y'], $l['gutter_y']]);

        $this->assertSame(['x' => 36.0, 'y' => 12.0, 'width' => 252.0, 'height' => 144.0], $l['front'][0]);
        $this->assertSame(['x' => 324.0, 'y' => 636.0, 'width' => 252.0, 'height' => 144.0], $l['front'][9]);
        $this->assertNoOverlapAndInside($l);
    }

    public function test_a4_centered_matches_reference_grid(): void
    {
        $l = CardSheetLayout::calculate(595.44, 841.68, 252, 144, CardSheetLayout::GAP_EVEN);

        // Reference sheet: 2 × 5 landscape cards, margins == gutters
        // (30.48 pt across, 20.28 pt down).
        $this->assertSame([2, 5, 10], [$l['cols'], $l['rows'], $l['count']]);
        $this->assertSame(['width' => 252.0, 'height' => 144.0], $l['card']);
        $this->assertSame([30.48, 30.48], [$l['margin_x'], $l['gutter_x']]);
        $this->assertSame([20.28, 20.28], [$l['margin_y'], $l['gutter_y']]);

        $xs = array_values(array_unique(array_column($l['front'], 'x')));
        $ys = array_values(array_unique(array_column($l['front'], 'y')));
        $this->assertEqualsWithDelta([30.48, 312.96], $xs, 1e-6);
        $this->assertEqualsWithDelta([20.28, 184.56, 348.84, 513.12, 677.4], $ys, 1e-6);
        $this->assertNoOverlapAndInside($l);
    }

    public function test_back_positions_match_front_positions_for_duplex(): void
    {
        foreach (BusinessCard::FORMATS as $key => $f) {
            foreach ($f['gutters'] ?? [null] as $gap) {
                $l = CardSheetLayout::calculate($f['width_pt'], $f['height_pt'], 252, 144, $f['gap'], $f['rotate'] ?? false, BusinessCard::gutterPt($key, $gap));
                $pos = fn($s) => sprintf('%.3f,%.3f', $s['x'], $s['y']);
                $front = array_map($pos, $l['front']);
                sort($front);
                foreach (['back', 'back_short'] as $side) {
                    $back = array_map($pos, $l[$side]);
                    sort($back);
                    $this->assertSame($front, $back, "{$key} gap {$gap} front/{$side} positions differ");
                }
            }
        }
    }

    public function test_7x10_ten_up_is_edge_to_edge_with_five_cuts(): void
    {
        $f = BusinessCard::FORMATS['seven_ten_10_up'];
        $l = CardSheetLayout::calculate($f['width_pt'], $f['height_pt'], 252, 144, $f['gap']);

        $this->assertSame([2, 5, 10], [$l['cols'], $l['rows'], $l['count']]);
        $this->assertSame([0.0, 0.0, 0.0, 0.0], [$l['margin_x'], $l['margin_y'], $l['gutter_x'], $l['gutter_y']]);
        $this->assertNoOverlapAndInside($l);
    }

    public function test_letter_sheets_fit_every_offered_gap_and_no_other(): void
    {
        $count = function (string $key, float $gutterPt) {
            $f = BusinessCard::FORMATS[$key];
            return CardSheetLayout::calculate($f['width_pt'], $f['height_pt'], 252, 144, $f['gap'], $f['rotate'] ?? false, $gutterPt)['count'];
        };

        foreach (['letter_12_up' => 12, 'letter_10_up' => 10, 'a4_12_up' => 12, 'a4_10_up' => 10] as $key => $cards) {
            foreach (BusinessCard::FORMATS[$key]['gutters'] as $n) {
                $this->assertSame($cards, $count($key, BusinessCard::gutterPt($key, $n)), "{$key} at {$n}/16 in");
            }
        }
        // 4 × 2 in + 3 × 3/16 in is wider than 8.5 in: 12-up stops at 1/8 in.
        $this->assertNotContains(3, BusinessCard::FORMATS['letter_12_up']['gutters']);
        $this->assertLessThan(12, $count('letter_12_up', 3 * BusinessCard::GUTTER_STEP_PT));
        // A4 is 8.27 in wide: 12-up stops at 1/16 in.
        $this->assertSame([1], BusinessCard::FORMATS['a4_12_up']['gutters']);
        $this->assertLessThan(12, $count('a4_12_up', 2 * BusinessCard::GUTTER_STEP_PT));
    }

    public function test_letter_12_up_turns_cards_sideways_with_an_eighth_inch_gap(): void
    {
        $l = CardSheetLayout::calculate(612, 792, 252, 144, CardSheetLayout::GAP_FIXED, true, BusinessCard::gutterPt('letter_12_up'));

        $this->assertSame([4, 3, 12], [$l['cols'], $l['rows'], $l['count']]);
        $this->assertSame([9.0, 9.0], [$l['gutter_x'], $l['gutter_y']]);
        // (8.5 - 4×2 - 3/8) / 2 = 1/16 in across, (11 - 3×3.5 - 1/4) / 2 = 1/8 in down.
        $this->assertSame([4.5, 9.0], [$l['margin_x'], $l['margin_y']]);
        $this->assertNoOverlapAndInside($l);
    }

    public function test_print_options_only_take_a_gap_the_sheet_offers(): void
    {
        $this->assertNull(BusinessCard::printOptions(['gap' => 1], 'six_up')['gap']);
        $this->assertSame(2, BusinessCard::printOptions([], 'letter_12_up')['gap']);
        $this->assertSame(1, BusinessCard::printOptions(['gap' => '1'], 'letter_12_up')['gap']);
        $this->assertSame(2, BusinessCard::printOptions(['gap' => 3], 'letter_12_up')['gap'], 'unlisted gap → default');
        $this->assertSame(3, BusinessCard::printOptions(['gap' => 3], 'letter_10_up')['gap']);

        $this->assertSame('business-card-8.5x11-10-up-gap-3-16in-bleed-1mm-short-edge.pdf',
            BusinessCard::variantFilename('letter_10_up', BusinessCard::printOptions(['gap' => 3, 'flip' => 'short'], 'letter_10_up')));
        $this->assertSame('business-card-7x10-6-up-bleed-1mm.pdf', BusinessCard::variantFilename('six_up', BusinessCard::printOptions([], 'six_up')));
        $this->assertSame('business-card-7x10-10-up.pdf', BusinessCard::variantFilename('seven_ten_10_up', BusinessCard::printOptions(['bleed' => 1.5], 'seven_ten_10_up')));
        $this->assertSame(1.0, BusinessCard::printOptions(['bleed' => 2], 'six_up')['bleed'], 'unlisted bleed -> default');
        $bleed = fn($f, $in) => BusinessCard::bleedPx($f, BusinessCard::printOptions($in, $f));
        $this->assertSame([0, 18, 12, 9, 9, 0], [
            $bleed('seven_ten_10_up', []),                        // cards touch: no room
            $bleed('six_up', ['bleed' => 1.5]),                   // 1.5 mm = 17.7 -> 18 px
            $bleed('letter_12_up', []),                           // 1 mm = 11.8 -> 12 px
            $bleed('letter_12_up', ['gap' => 1, 'bleed' => 1.5]), // half of 1/16 in = 9.4 -> 9 px
            $bleed('a4_12_up', []),                               // A4 12-up: 1/16 in gap -> 9 px
            $bleed('six_up', ['test' => 1]),                      // alignment test: outlines only
        ]);
        $this->assertSame(['1/16', '1/8', '3/16', '1/4'], array_map([BusinessCard::class, 'sixteenths'], [1, 2, 3, 4]));
    }

    public function test_7x10_six_up_turns_cards_with_quarter_inch_gaps(): void
    {
        $l = CardSheetLayout::calculate(504, 720, 252, 144, CardSheetLayout::GAP_FIXED, true, 18);

        $this->assertTrue($l['rotated']);
        $this->assertSame([3, 2, 6], [$l['cols'], $l['rows'], $l['count']]);
        $this->assertSame(['width' => 144.0, 'height' => 252.0], $l['card'], 'each slot is the card turned 90°, unscaled');
        $this->assertSame([18.0, 18.0], [$l['gutter_x'], $l['gutter_y']]);
        // Centred: (7 - 3×2 - 2×0.25) / 2 = 0.25 in across, (10 - 2×3.5 - 0.25) / 2 = 1.375 in down.
        $this->assertSame([18.0, 99.0], [$l['margin_x'], $l['margin_y']]);
        $this->assertSame([18.0, 180.0, 342.0], array_values(array_unique(array_column($l['front'], 'x'))));
        $this->assertSame([99.0, 369.0], array_values(array_unique(array_column($l['front'], 'y'))));
        $this->assertNoOverlapAndInside($l);
    }

    public function test_gutter_only_allowed_for_fixed_gaps(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CardSheetLayout::calculate(504, 720, 252, 144, CardSheetLayout::GAP_NONE, false, 18);
    }

    public function test_side_turns_keep_both_sides_upright_after_the_flip(): void
    {
        $turns = [\App\Services\BusinessCard\BusinessCardPdfService::class, 'sideTurns'];
        $this->assertSame(['front' => 0, 'back' => 0], $turns(false, false));
        $this->assertSame(['front' => 0, 'back' => 180], $turns(false, true));
        // Intervention/GD degrees: -90 = clockwise, 90 = counter-clockwise.
        $this->assertSame(['front' => -90, 'back' => 90], $turns(true, false));
        $this->assertSame(['front' => -90, 'back' => -90], $turns(true, true));
    }

    public function test_never_counts_partial_cards_and_rejects_bad_input(): void
    {
        $l = CardSheetLayout::calculate(503.9, 1000, 252, 144, CardSheetLayout::GAP_NONE);
        $this->assertSame([1, 6], [$l['cols'], $l['rows']]); // 1.99 cards wide → 1 column
        $this->assertSame(['width' => 252.0, 'height' => 144.0], $l['card']);

        // Cards are only turned when asked: a sheet narrower than 3.5 in fits none.
        try {
            CardSheetLayout::calculate(251.9, 1000, 252, 144, CardSheetLayout::GAP_NONE);
            $this->fail('Expected no fit');
        } catch (\RuntimeException) {
        }

        $this->expectException(\RuntimeException::class);
        CardSheetLayout::calculate(100, 100, 252, 144, CardSheetLayout::GAP_NONE);
    }

    public function test_rejects_unknown_gap_mode(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CardSheetLayout::calculate(504, 720, 252, 144, 'random');
    }

    private function assertNoOverlapAndInside(array $l): void
    {
        $slots = $l['front'];
        foreach ($slots as $i => $a) {
            $this->assertGreaterThanOrEqual(-1e-6, $a['x']);
            $this->assertGreaterThanOrEqual(-1e-6, $a['y']);
            $this->assertLessThanOrEqual($l['sheet']['width'] + 1e-6, $a['x'] + $a['width']);
            $this->assertLessThanOrEqual($l['sheet']['height'] + 1e-6, $a['y'] + $a['height']);
            foreach (array_slice($slots, $i + 1) as $b) {
                $overlapX = min($a['x'] + $a['width'], $b['x'] + $b['width']) - max($a['x'], $b['x']);
                $overlapY = min($a['y'] + $a['height'], $b['y'] + $b['height']) - max($a['y'], $b['y']);
                $this->assertFalse($overlapX > 1e-6 && $overlapY > 1e-6, 'Cards overlap');
            }
        }
    }
}
