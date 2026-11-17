<?php

namespace App\Services;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * Decodes a `data:image/png|jpeg;base64,...` URI into verified image bytes.
 * Shared by every endpoint that accepts canvas renders from the browser.
 */
class DataUriImage
{
    /**
     * @return array{bin: string, type: 'png'|'jpeg', width: int, height: int}
     * @throws \InvalidArgumentException with a user-facing reason
     */
    public static function decode(mixed $dataUri, int $maxBytes): array
    {
        if (!is_string($dataUri) || !preg_match('#^data:image/(png|jpe?g);base64,#i', $dataUri, $m)) {
            throw new \InvalidArgumentException('must be a PNG or JPEG data URI.');
        }

        $bin = base64_decode(substr($dataUri, strlen($m[0])), true);
        if ($bin === false || $bin === '') {
            throw new \InvalidArgumentException('could not be decoded.');
        }
        if (strlen($bin) > $maxBytes) {
            throw new \InvalidArgumentException('is too large.');
        }

        $info = @getimagesizefromstring($bin);
        if (!$info || !in_array($info[2] ?? null, [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
            throw new \InvalidArgumentException('is not a valid PNG or JPEG.');
        }

        return [
            'bin'    => $bin,
            'type'   => $info[2] === IMAGETYPE_PNG ? 'png' : 'jpeg',
            'width'  => (int) $info[0],
            'height' => (int) $info[1],
        ];
    }

    /** Re-encode through GD: strips metadata and anything appended to the bytes. */
    public static function reencode(string $bin, string $type, ?int $width = null, ?int $height = null, ?int $dpi = null): string
    {
        $image = (new ImageManager(new Driver()))->read($bin);
        if ($width && $height) {
            $image->resize($width, $height);
        }

        if ($type === 'png') {
            return (string) $image->toPng();
        }
        $jpeg = (string) $image->toJpeg(95);

        return $dpi ? self::setJpegDpi($jpeg, $dpi) : $jpeg;
    }

    /**
     * Stamp the JFIF density (pixels per inch) so the JPEG opens at its real
     * print size, e.g. 1050×600 px @ 300 DPI = 3.5 × 2 in. GD writes 96 DPI.
     */
    public static function setJpegDpi(string $jpeg, int $dpi): string
    {
        if (strncmp($jpeg, "\xFF\xD8", 2) !== 0) {
            return $jpeg;
        }
        $density = chr(1) . pack('nn', $dpi, $dpi); // units = dots per inch

        if (substr($jpeg, 2, 2) === "\xFF\xE0" && substr($jpeg, 6, 5) === "JFIF\0") {
            return substr_replace($jpeg, $density, 13, 5);
        }

        // No JFIF header: insert one right after SOI.
        $app0 = "\xFF\xE0" . pack('n', 16) . "JFIF\0" . "\x01\x01" . $density . "\x00\x00";

        return "\xFF\xD8" . $app0 . substr($jpeg, 2);
    }
}
