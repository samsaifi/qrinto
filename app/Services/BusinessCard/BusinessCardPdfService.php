<?php

namespace App\Services\BusinessCard;

use App\Models\UserDesign;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * Builds the print sheets for a saved business card from its stored side
 * renders (never from client-supplied sizes). Every format is one PDF:
 *   page 1 = front sheet, page 2 = back sheet
 * using the same page size and duplex-aligned card positions, matching the
 * local-print preview which prints a multi-page PDF with a duplex option.
 */
class BusinessCardPdfService
{
    /**
     * Generate every missing sheet. Existing PDFs are kept unless $force.
     * Failures are recorded per format on the design and never thrown, so the
     * saved artwork is not lost if PDF generation breaks.
     *
     * @return array<string, string> format => error message (empty on success)
     */
    public function generateAll(UserDesign $design, bool $force = false): array
    {
        $errors = [];
        foreach (array_keys(BusinessCard::FORMATS) as $format) {
            try {
                $this->generate($design, $format, $force);
            } catch (\Throwable $e) {
                Log::error('Business card PDF generation failed', [
                    'design_id' => $design->id,
                    'format'    => $format,
                    'error'     => $e->getMessage(),
                ]);
                $errors[$format] = 'The ' . BusinessCard::format($format)['label'] . ' PDF could not be generated.';
            }
        }

        $data = $design->design_data;
        $data['pdf_errors'] = $errors;
        $design->design_data = $data;
        $design->save();

        return $errors;
    }

    /**
     * Generate one format (optionally a print variant) and return its storage
     * path. The default variant (long-edge flip, no nudge) is the one recorded
     * on the design and created at save time; other variants are built on
     * demand when that exact combination is printed, then reused.
     *
     * @param  array{flip?: string, dx?: float, dy?: float, test?: bool, gap?: int}  $options
     */
    public function generate(UserDesign $design, string $format, bool $force = false, array $options = []): string
    {
        $spec = BusinessCard::format($format);
        $opts = BusinessCard::printOptions($options, $format);
        $data = $design->design_data;
        $disk = Storage::disk($data['disk'] ?? BusinessCardDesignService::DISK);
        $isDefault = $opts == BusinessCard::printOptions([], $format);

        $filename = BusinessCard::variantFilename($format, $opts);
        $path = 'business-cards/' . $data['token'] . '/' . $filename;
        if (!$force && $disk->exists($path)) {
            return $path;
        }

        foreach (array_keys(BusinessCard::SIDES) as $side) {
            $sidePath = $data['sides'][$side]['image_path'] ?? null;
            if (!$sidePath || !$disk->exists($sidePath)) {
                throw new \RuntimeException("Missing {$side} side image.");
            }
        }

        $layout = $this->layout($format, $opts['gap']);
        $short  = $opts['flip'] === 'short';
        $turns  = self::sideTurns($layout['rotated'], $short);
        // Bleed: artwork with its edges mirrored out by $bleedPx, printed in a
        // box that much larger than the card on every side.
        // bleedPx() is in 300 DPI pixels; scale it to this design's render DPI
        // (rounded down, so the grid's room for bleed is never exceeded).
        $dpi = BusinessCard::dpi($data['dpi'] ?? BusinessCard::DPI);
        $bleedPx = (int) floor(BusinessCard::bleedPx($format, $opts) * $dpi / BusinessCard::DPI + 1e-6);
        $images = [];
        foreach (['front', 'back'] as $side) {
            $sideImage = $data['sides'][$side]['image_path'];
            if ($bleedPx > 0) {
                $sideImage = $this->bled($disk, $sideImage, $bleedPx);
            }
            $images[$side] = $this->absolute($disk, $turns[$side] ? $this->rotated($disk, $sideImage, $turns[$side]) : $sideImage);
        }

        // Fine-tune: move every back together, in back-page coordinates.
        $mm = 72 / 25.4;
        $b  = $bleedPx / $dpi * BusinessCard::POINTS_PER_INCH;
        $box = fn(array $s, float $dx = 0, float $dy = 0) => array_merge($s, [
            'x'      => round($s['x'] + $dx - $b, 3),
            'y'      => round($s['y'] + $dy - $b, 3),
            'width'  => round($s['width'] + 2 * $b, 3),
            'height' => round($s['height'] + 2 * $b, 3),
        ]);
        $frontSlots = array_map($box, $layout['front']);
        $backSlots = array_map(fn($s) => $box($s, $opts['dx'] * $mm, $opts['dy'] * $mm), $short ? $layout['back_short'] : $layout['back']);

        $pdf = Pdf::loadView('business-card.pdf.sheet', [
            'layout' => array_merge($layout, ['front' => $frontSlots, 'back' => $backSlots]),
            'images' => $images,
            'test'   => $opts['test'],
        ]);
        $pdf->setPaper([0, 0, $spec['width_pt'], $spec['height_pt']]);
        $disk->put($path, $pdf->output());

        if ($isDefault) {
            $data['pdfs'][$format] = [
                'path'      => $path,
                'filename'  => $filename,
                'width_pt'  => $spec['width_pt'],
                'height_pt' => $spec['height_pt'],
                'cards'     => $layout['count'],
            ];
            unset($data['pdf_errors'][$format]);
            $design->design_data = $data;
            $design->save();
        }

        return $path;
    }

    protected function absolute($disk, string $path): string
    {
        return str_replace('\\', '/', $disk->path($path));
    }

    /** Cached copy of a side render turned by $degrees (pixels rotated, not CSS). */
    protected function rotated($disk, string $path, int $degrees): string
    {
        $target = preg_replace('/\.jpg$/', '', $path) . '-r' . $degrees . '.jpg';
        if (!$disk->exists($target)) {
            $image = (new ImageManager(new Driver()))->read($disk->get($path))->rotate($degrees);
            $disk->put($target, (string) $image->toJpeg(95));
        }

        return $target;
    }

    /** Sheet layout for a format (and selectable gutter) - the single source for PDF positions. */
    public function layout(string $format, ?int $gap = null): array
    {
        return BusinessCard::layout($format, $gap);
    }

    /**
     * Cached copy of a side render grown by $px on every side, the new margin
     * filled with the artwork's own edges mirrored outward (like a photo-lab
     * bleed), so the card area keeps its exact pixels and position.
     */
    public function bled($disk, string $path, int $px): string
    {
        $target = preg_replace('/\.jpg$/', '', $path) . '-bleed' . $px . '.jpg';
        if ($disk->exists($target)) {
            return $target;
        }

        $src = imagecreatefromstring($disk->get($path));
        $w = imagesx($src);
        $h = imagesy($src);
        $p = min($px, $w, $h);
        $out = imagecreatetruecolor($w + 2 * $p, $h + 2 * $p);
        imagecopy($out, $src, $p, $p, 0, 0, $w, $h);

        // Left / right: the outermost columns, mirrored.
        $flipH = imagecreatetruecolor($w, $h);
        imagecopy($flipH, $src, 0, 0, 0, 0, $w, $h);
        imageflip($flipH, IMG_FLIP_HORIZONTAL);
        imagecopy($out, $flipH, 0, $p, $w - $p, 0, $p, $h);
        imagecopy($out, $flipH, $p + $w, $p, 0, 0, $p, $h);

        // Top / bottom (corners included): the outermost rows, mirrored.
        $ow = $w + 2 * $p;
        $oh = $h + 2 * $p;
        $flipV = imagecreatetruecolor($ow, $oh);
        imagecopy($flipV, $out, 0, 0, 0, 0, $ow, $oh);
        imageflip($flipV, IMG_FLIP_VERTICAL);
        imagecopy($out, $flipV, 0, 0, 0, $h, $ow, $p);
        imagecopy($out, $flipV, 0, $p + $h, 0, $p, $ow, $p);

        ob_start();
        imagejpeg($out, null, 95);
        $disk->put($target, ob_get_clean());

        return $target;
    }

    /**
     * How far each side's artwork is turned on the sheet, in Intervention/GD
     * degrees (positive = counter-clockwise), so both sides read the right way
     * up once the sheet is turned over and the cards are cut:
     *   upright cards:  front 0,          back 0 (long edge) / 180 (short edge)
     *   turned cards:   front clockwise,  back counter-clockwise (long edge) /
     *                                     clockwise (short edge)
     *
     * @return array{front: int, back: int}
     */
    public static function sideTurns(bool $rotated, bool $shortEdge): array
    {
        if (!$rotated) {
            return ['front' => 0, 'back' => $shortEdge ? 180 : 0];
        }

        return ['front' => -90, 'back' => $shortEdge ? -90 : 90];
    }
}
