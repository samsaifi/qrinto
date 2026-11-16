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
    public static function reencode(string $bin, string $type, ?int $width = null, ?int $height = null): string
    {
        $image = (new ImageManager(new Driver()))->read($bin);
        if ($width && $height) {
            $image->resize($width, $height);
        }

        return (string) ($type === 'png' ? $image->toPng() : $image->toJpeg(95));
    }
}
