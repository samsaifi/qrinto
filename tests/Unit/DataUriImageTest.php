<?php

namespace Tests\Unit;

use App\Services\DataUriImage;
use PHPUnit\Framework\TestCase;

class DataUriImageTest extends TestCase
{
    private function png(string $append = ''): string
    {
        $im = imagecreatetruecolor(20, 10);
        ob_start();
        imagepng($im);
        return 'data:image/png;base64,' . base64_encode(ob_get_clean() . $append);
    }

    public function test_decodes_png(): void
    {
        $d = DataUriImage::decode($this->png(), 1_000_000);
        $this->assertSame(['png', 20, 10], [$d['type'], $d['width'], $d['height']]);
    }

    public function test_rejects_oversize_non_image_and_bad_scheme(): void
    {
        foreach ([[$this->png(), 10], ['data:image/png;base64,' . base64_encode('not an image'), 1000], ['data:image/svg+xml;base64,PHN2Zy8+', 1000], [null, 1000]] as [$uri, $max]) {
            try {
                DataUriImage::decode($uri, $max);
                $this->fail('Expected rejection');
            } catch (\InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_reencode_strips_appended_payload(): void
    {
        $d = DataUriImage::decode($this->png('<?php echo 1; ?>'), 1_000_000);
        $this->assertStringContainsString('<?php', $d['bin']);
        $this->assertStringNotContainsString('<?php', DataUriImage::reencode($d['bin'], 'png'));
    }
}
