<?php

namespace App\Services\BusinessCard;

use App\Models\UserDesign;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Services\DataUriImage;

/**
 * Persists a double-sided business card using the existing `user_designs`
 * storage (one UserDesign row = one card, both sides in `design_data`).
 *
 * Files live on the PRIVATE disk under business-cards/{token}/ so they are only
 * reachable through the access-checked download route:
 *   front.json / back.json  - editor (Fabric.js) serialisation per side
 *   front.jpg  / back.jpg   - server re-encoded render per side, at the chosen DPI (300 / 600 / 1200)
 *   *.pdf                   - generated print sheets
 */
class BusinessCardDesignService
{
    public const DISK = 'local';
    public const TYPE = 'business-card';

    /** Max decoded size of one side image. */
    public const MAX_IMAGE_BYTES = 16 * 1024 * 1024;

    /** Max encoded size of one side's editor JSON. */
    public const MAX_DESIGN_BYTES = 8 * 1024 * 1024;

    /** Allowed deviation from the 3.5:2 aspect ratio of a submitted render. */
    public const ASPECT_TOLERANCE = 0.01;

    /** Accepted render widths (150 DPI .. 1200 DPI at 3.5 in). */
    public const MIN_IMAGE_WIDTH = 525;
    public const MAX_IMAGE_WIDTH = 4200;

    public static function isBusinessCardDesign(UserDesign $design): bool
    {
        return ($design->design_data['type'] ?? null) === self::TYPE;
    }

    /**
     * Create (or overwrite the sides of) a business card design.
     *
     * @param  array{front: array{image: string, design: array}, back: array{image: string, design: array}}  $sides
     */
    public function save(array $sides, int $productId, ?int $userId, string $sessionId, ?UserDesign $existing = null, int $dpi = BusinessCard::DPI): UserDesign
    {
        $dpi = BusinessCard::dpi($dpi);
        // Validate + normalise everything before touching storage, so a bad
        // back side never leaves a half-written design behind.
        $prepared = [];
        foreach (array_keys(BusinessCard::SIDES) as $side) {
            $prepared[$side] = [
                'image'  => $this->normaliseImage($sides[$side]['image'] ?? null, $side, $dpi),
                'design' => $this->encodeDesign($sides[$side]['design'] ?? null, $side),
            ];
        }

        $data  = $existing?->design_data ?? [];
        $token = $data['token'] ?? Str::random(32);
        $dir   = 'business-cards/' . $token;
        $disk  = Storage::disk(self::DISK);

        $sideData = [];
        foreach ($prepared as $side => $p) {
            $disk->put("{$dir}/{$side}.jpg", $p['image']);
            $disk->put("{$dir}/{$side}.json", $p['design']);
            $sideData[$side] = [
                'image_path'  => "{$dir}/{$side}.jpg",
                'design_path' => "{$dir}/{$side}.json",
                'width_px'    => BusinessCard::imageWidthPx($dpi),
                'height_px'   => BusinessCard::imageHeightPx($dpi),
            ];
        }

        // New artwork invalidates every previously generated sheet and variant
        // (short-edge, nudged, test) plus derived rotated / bleed renders.
        foreach ($existing ? $disk->files($dir) : [] as $file) {
            if (str_ends_with($file, '.pdf') || preg_match('/-(r-?\d+|bleed\d+)\.jpg$/', $file)) {
                $disk->delete($file);
            }
        }

        $designData = [
            'type'        => self::TYPE,
            'version'     => 1,
            'token'       => $token,
            'disk'        => self::DISK,
            'width_in'    => BusinessCard::WIDTH_IN,
            'height_in'   => BusinessCard::HEIGHT_IN,
            'orientation' => BusinessCard::ORIENTATION,
            'dpi'         => $dpi,
            'sides'       => $sideData,
            'pdfs'        => [],
            'pdf_errors'  => [],
        ];

        $design = $existing ?? new UserDesign();
        $design->fill([
            'user_id'            => $userId,
            'session_id'         => $sessionId,
            'product_id'         => $productId,
            'design_data'        => $designData,
            'preview_image_path' => null, // side renders are private - see design_data.sides
        ]);
        $design->save();

        return $design;
    }

    /**
     * Decode a data-URI render, verify it is a real JPEG/PNG with the card's
     * aspect ratio, then re-encode to exactly the DPI's size, e.g. 1050×600 at 300 (strips metadata and
     * anything appended to the image bytes).
     */
    protected function normaliseImage(mixed $dataUri, string $side, int $dpi = BusinessCard::DPI): string
    {
        try {
            $img = DataUriImage::decode($dataUri, self::MAX_IMAGE_BYTES);
        } catch (\InvalidArgumentException $e) {
            $this->fail("sides.{$side}.image", 'The ' . $side . ' side image ' . $e->getMessage());
        }

        ['bin' => $bin, 'width' => $w, 'height' => $h] = $img;
        $expected = BusinessCard::WIDTH_IN / BusinessCard::HEIGHT_IN;
        if ($w < self::MIN_IMAGE_WIDTH || $w > self::MAX_IMAGE_WIDTH || $h <= 0
            || abs(($w / $h) - $expected) / $expected > self::ASPECT_TOLERANCE) {
            $this->fail(
                "sides.{$side}.image",
                'The ' . $side . ' side must be a landscape 3.5 × 2 in render (got ' . $w . '×' . $h . ' px).'
            );
        }

        return DataUriImage::reencode($bin, 'jpeg', BusinessCard::imageWidthPx($dpi), BusinessCard::imageHeightPx($dpi));
    }

    /** Validate one side's editor serialisation and return it as JSON. */
    protected function encodeDesign(mixed $design, string $side): string
    {
        if (!is_array($design) || !isset($design['objects']) || !is_array($design['objects'])) {
            $this->fail("sides.{$side}.design", 'The ' . $side . ' side design data is invalid.');
        }

        $this->assertSafeReferences($design, "sides.{$side}.design");

        $json = json_encode($design, JSON_UNESCAPED_SLASHES);
        if ($json === false || strlen($json) > self::MAX_DESIGN_BYTES) {
            $this->fail("sides.{$side}.design", 'The ' . $side . ' side design data is too large.');
        }

        return $json;
    }

    /**
     * The server never dereferences editor image sources (PDFs are built from
     * the rendered side images), but stored designs are reloaded into the
     * editor - so only inline images and http(s) URLs are accepted.
     */
    protected function assertSafeReferences(array $node, string $attribute, int $depth = 0): void
    {
        if ($depth > 32) {
            $this->fail($attribute, 'The design data is nested too deeply.');
        }

        foreach ($node as $key => $value) {
            if (is_array($value)) {
                $this->assertSafeReferences($value, $attribute, $depth + 1);
            } elseif (in_array($key, ['src', 'href', 'source'], true) && is_string($value) && !$this->isSafeReference($value)) {
                $this->fail($attribute, 'The design contains an unsupported file reference.');
            }
        }
    }

    protected function isSafeReference(string $ref): bool
    {
        if (preg_match('#^data:image/(png|jpe?g|webp|gif|svg\+xml)[;,]#i', $ref)) {
            return true;
        }

        $parts = parse_url($ref);
        return is_array($parts)
            && in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            && !empty($parts['host'])
            && !str_contains($parts['path'] ?? '', '..');
    }

    protected function fail(string $attribute, string $message): never
    {
        throw ValidationException::withMessages([$attribute => $message]);
    }
}
