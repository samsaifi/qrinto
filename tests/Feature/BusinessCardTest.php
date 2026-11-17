<?php

namespace Tests\Feature;

use App\Models\UserDesign;
use App\Services\BusinessCard\BusinessCard;
use App\Services\BusinessCard\BusinessCardPdfService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Tests\TestCase;

/**
 * Business card save + PDF generation.
 *
 * The project's full migration set does not run on sqlite, so only the tables
 * this feature touches are created: minimal users/products, and the REAL
 * user_designs migration.
 */
class BusinessCardTest extends TestCase
{
    private const SESSION_A = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
    private const SESSION_B = 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb';

    protected function setUp(): void
    {
        parent::setUp();

        // .env APP_URL has a /qrinto sub-path the in-process test kernel can't route.
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name')->nullable();
            $t->string('email')->nullable();
            $t->string('password')->nullable();
            $t->timestamps();
        });
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->string('name')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->softDeletes();
        });
        (require database_path('migrations/2026_02_23_162403_create_user_designs_table.php'))->up();

        DB::table('products')->insert(['id' => 1, 'name' => 'Card']);
        DB::table('users')->insert([['id' => 7, 'name' => 'Owner'], ['id' => 8, 'name' => 'Other']]);

        Storage::fake('local');
    }

    /* ── Helpers ─────────────────────────────────────────────── */

    /** Solid side render with a 100×60 marker block in the top-left corner. */
    private function sideImage(array $rgb, int $w = 1050, int $h = 600, string $type = 'jpeg'): string
    {
        $im = imagecreatetruecolor($w, $h);
        imagefill($im, 0, 0, imagecolorallocate($im, ...$rgb));
        imagefilledrectangle($im, 0, 0, 99, 59, imagecolorallocate($im, 0, 0, 0));
        ob_start();
        $type === 'png' ? imagepng($im) : imagejpeg($im, null, 95);
        return 'data:image/' . $type . ';base64,' . base64_encode(ob_get_clean());
    }

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'product_id'  => 1,
            'width_in'    => 3.5,
            'height_in'   => 2,
            'orientation' => 'landscape',
            'sides'       => [
                'front' => [
                    'image'  => $this->sideImage([220, 30, 30]),
                    'design' => ['background' => '#ffffff', 'objects' => [['type' => 'textbox', 'text' => 'FRONT NAME']]],
                ],
                'back' => [
                    'image'  => $this->sideImage([30, 30, 220]),
                    'design' => ['background' => '#eeeeee', 'objects' => [['type' => 'textbox', 'text' => 'BACK LOGO']]],
                ],
            ],
        ], $overrides);
    }

    private function asSession(string $id): static
    {
        // withCredentials: JSON test requests only send cookies when enabled.
        return $this->withCredentials()->withCookie(config('session.cookie'), $id);
    }

    private function save(array $payload, string $session = self::SESSION_A)
    {
        return $this->asSession($session)->postJson('/print/own-design/business-card/save', $payload);
    }

    /** @return array{pages: list<array{box: array, cards: list<array>}>, images: list<array>} */
    private function parsePdf(string $binary): array
    {
        $pdf   = (new Parser())->parseContent($binary);
        $pages = [];
        foreach ($pdf->getPages() as $page) {
            $box     = $page->getDetails()['MediaBox'];
            $content = $page->get('Contents')->getContent();
            preg_match_all('/([\d.]+) 0 0 ([\d.]+) ([\d.\-]+) ([\d.\-]+) cm \/(I\d+) Do/', $content, $m, PREG_SET_ORDER);
            $cards = array_map(fn($x) => [
                'w' => (float) $x[1], 'h' => (float) $x[2],
                'x' => (float) $x[3], 'y' => (float) $x[4], 'ref' => $x[5],
            ], $m);
            $pages[] = ['box' => array_map('floatval', $box), 'cards' => $cards];
        }

        $images = [];
        foreach ($pdf->getObjectsByType('XObject', 'Image') as $img) {
            $d = $img->getDetails();
            $images[] = ['w' => (int) $d['Width'], 'h' => (int) $d['Height'], 'filter' => $d['Filter'] ?? null];
        }

        return ['pages' => $pages, 'images' => $images];
    }

    private function design(int $id): UserDesign
    {
        return UserDesign::findOrFail($id);
    }

    /* ── Save ────────────────────────────────────────────────── */

    public function test_saves_both_sides_on_one_design_and_returns_pdf_links(): void
    {
        $res = $this->save($this->payload())->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('orientation', 'landscape')
            ->assertJsonPath('width_in', 3.5)
            ->assertJsonPath('height_in', 2)
            ->assertJsonPath('sides', ['front', 'back'])
            ->assertJsonPath('pdf_errors', [])
            ->assertJsonPath('pdfs.seven_ten_10_up.filename', 'business-card-7x10-10-up.pdf')
            ->assertJsonPath('pdfs.letter_12_up.filename', 'business-card-8.5x11-12-up-gap-1-8in-bleed-1mm.pdf')
            ->assertJsonPath('pdfs.letter_10_up.filename', 'business-card-8.5x11-10-up-gap-1-8in-bleed-1mm.pdf')
            ->assertJsonPath('pdfs.a4_centered.filename', 'business-card-a4-centered-bleed-1mm.pdf')
            ->assertJsonPath('pdfs.six_up.filename', 'business-card-7x10-6-up-bleed-1mm.pdf');

        $this->assertSame(1, UserDesign::count());
        $design = $this->design($res->json('design_id'));
        $data   = $design->design_data;

        $this->assertSame('business-card', $data['type']);
        $this->assertSame(self::SESSION_A, $design->session_id);
        $this->assertNull($design->user_id);

        // Each side keeps its own artwork + editor JSON.
        $disk = Storage::disk('local');
        $front = json_decode($disk->get($data['sides']['front']['design_path']), true);
        $back  = json_decode($disk->get($data['sides']['back']['design_path']), true);
        $this->assertSame('FRONT NAME', $front['objects'][0]['text']);
        $this->assertSame('BACK LOGO', $back['objects'][0]['text']);

        foreach (['front' => [220, 30, 30], 'back' => [30, 30, 220]] as $side => $rgb) {
            $bin = $disk->get($data['sides'][$side]['image_path']);
            $this->assertSame([1050, 600, IMAGETYPE_JPEG], array_slice(getimagesizefromstring($bin), 0, 3));
            $im = imagecreatefromstring($bin);
            $c = imagecolorsforindex($im, imagecolorat($im, 800, 400));
            $this->assertEqualsWithDelta($rgb, [$c['red'], $c['green'], $c['blue']], 12, "{$side} colour");
        }

        $this->assertTrue($disk->exists($data['pdfs']['letter_10_up']['path']));
        $this->assertTrue($disk->exists($data['pdfs']['a4_centered']['path']));
    }

    public function test_multipart_file_upload_save_is_accepted_and_stamped_300_dpi(): void
    {
        $p = $this->payload(['dpi' => 300]);
        $files = [];
        foreach (['front', 'back'] as $side) {
            $bin = base64_decode(explode(',', $p['sides'][$side]['image'])[1]);
            $files["{$side}_image"] = \Illuminate\Http\UploadedFile::fake()->createWithContent("{$side}.jpg", $bin);
            unset($p['sides'][$side]['image']);
        }
        $files['payload'] = \Illuminate\Http\UploadedFile::fake()->createWithContent('payload.json', json_encode($p));

        $res = $this->asSession(self::SESSION_A)
            ->post('/print/own-design/business-card/save', $files, ['Accept' => 'application/json'])
            ->assertCreated()->assertJsonPath('success', true);

        $data = $this->design($res->json('design_id'))->design_data;
        $jpg  = Storage::disk('local')->get($data['sides']['front']['image_path']);
        $this->assertSame([1050, 600], array_slice(getimagesizefromstring($jpg), 0, 2));
        $this->assertSame(['u' => 1, 'x' => 300, 'y' => 300], unpack('Cu/nx/ny', substr($jpg, 13, 5)));
    }

    public function test_letter_10_up_pdf_is_exact_8_5x11_with_10_unscaled_cards_per_side(): void
    {
        $id  = $this->save($this->payload())->assertCreated()->json('design_id');
        $pdf = $this->parsePdf(Storage::disk('local')->get($this->design($id)->design_data['pdfs']['letter_10_up']['path']));

        $this->assertCount(2, $pdf['pages'], 'front page + back page');
        foreach ($pdf['pages'] as $page) {
            $this->assertSame([0.0, 0.0, 612.0, 792.0], $page['box']);
            $this->assertCount(10, $page['cards']);
            foreach ($page['cards'] as $c) {
                $this->assertSame([257.76, 149.76], [$c['w'], $c['h']], 'card is exactly 3.5 × 2 in plus 1 mm (12 px) bleed a side');
            }
        }

        $this->assertSamePositions($pdf['pages'][0]['cards'], $pdf['pages'][1]['cards']);
        $this->assertNotSame($pdf['pages'][0]['cards'][0]['ref'], $pdf['pages'][1]['cards'][0]['ref'], 'back uses the back image');

        // Embedded at 300 DPI (no resampling): the render plus 12 px mirrored bleed a side.
        $this->assertCount(2, $pdf['images']);
        foreach ($pdf['images'] as $img) {
            $this->assertSame([1074, 624, 'DCTDecode'], [$img['w'], $img['h'], $img['filter']]);
        }
    }

    public function test_a4_pdf_matches_reference_centered_grid(): void
    {
        $id   = $this->save($this->payload())->assertCreated()->json('design_id');
        $data = $this->design($id)->design_data;
        $pdf  = $this->parsePdf(Storage::disk('local')->get($data['pdfs']['a4_centered']['path']));

        // Reference sheet (PDF coordinates, origin bottom-left): 2 × 5 landscape
        // cards, 30.48 pt between/around columns, 20.28 pt between/around rows.
        $expected = [];
        foreach ([677.4, 513.12, 348.84, 184.56, 20.28] as $y) {
            foreach ([30.48, 312.96] as $x) {
                $expected[] = ['x' => $x, 'y' => $y, 'w' => 252.0, 'h' => 144.0];
            }
        }

        $this->assertCount(2, $pdf['pages']);
        foreach ($pdf['pages'] as $page) {
            $this->assertSame([0.0, 0.0, 595.44, 841.68], $page['box']);
            $this->assertCount(10, $page['cards']);
            $this->assertSamePositions($this->bledBoxes($expected), $page['cards']);
        }
        $this->assertNotSame($pdf['pages'][0]['cards'][0]['ref'], $pdf['pages'][1]['cards'][0]['ref'], 'back uses the back image');

        // Cards keep their landscape artwork (plus the mirrored bleed) - not rotated.
        $this->assertCount(2, $pdf['images']);
        foreach ($pdf['images'] as $img) {
            $this->assertSame([1074, 624, 'DCTDecode'], [$img['w'], $img['h'], $img['filter']]);
        }
        $this->assertSame(10, $data['pdfs']['a4_centered']['cards']);
        // Turned copies of the artwork exist only for the sideways (6- and 12-per-sheet) layouts.
        $this->assertSame(
            // Bleed copies: 12 px (1 mm) on every sheet with room, 9 px on A4 12-up
            // (half its 1/16 in gap); none on the edge-to-edge 7 × 10.
            ['back-bleed12-r90.jpg', 'back-bleed12.jpg', 'back-bleed9-r90.jpg', 'back-bleed9.jpg', 'back.jpg', 'back.json',
                'business-card-7x10-10-up.pdf', 'business-card-7x10-6-up-bleed-1mm.pdf',
                'business-card-8.5x11-10-up-gap-1-8in-bleed-1mm.pdf', 'business-card-8.5x11-12-up-gap-1-8in-bleed-1mm.pdf',
                'business-card-a4-10-up-gap-1-8in-bleed-1mm.pdf', 'business-card-a4-12-up-gap-1-16in-bleed-1mm.pdf',
                'business-card-a4-centered-bleed-1mm.pdf', 'front-bleed12-r-90.jpg', 'front-bleed12.jpg',
                'front-bleed9-r-90.jpg', 'front-bleed9.jpg', 'front.jpg', 'front.json'],
            collect(Storage::disk('local')->files('business-cards/' . $data['token']))->map(fn($f) => basename($f))->sort()->values()->all()
        );
    }

    public function test_six_up_pdf_turns_cards_upright_with_matching_backs(): void
    {
        $id   = $this->save($this->payload())->assertCreated()->json('design_id');
        $data = $this->design($id)->design_data;
        $pdf  = $this->parsePdf(Storage::disk('local')->get($data['pdfs']['six_up']['path']));

        // PDF coordinates (origin bottom-left): x 0.25 / 2.5 / 4.75 in, y 1.375 / 5.125 in.
        $expected = [];
        foreach ([99.0, 369.0] as $y) {
            foreach ([18.0, 180.0, 342.0] as $x) {
                $expected[] = ['x' => $x, 'y' => $y, 'w' => 144.0, 'h' => 252.0];
            }
        }
        $this->assertCount(2, $pdf['pages']);
        foreach ($pdf['pages'] as $page) {
            $this->assertSame([0.0, 0.0, 504.0, 720.0], $page['box']);
            $this->assertCount(6, $page['cards']);
            $this->assertSamePositions($this->bledBoxes($expected), $page['cards']);
        }
        // Artwork (with 12 px bleed) turned in pixels, no stretching: 624 × 1074 per side.
        $this->assertCount(2, $pdf['images']);
        foreach ($pdf['images'] as $img) {
            $this->assertSame([624, 1074, 'DCTDecode'], [$img['w'], $img['h'], $img['filter']]);
        }

        $dir  = 'business-cards/' . $data['token'];
        $disk = Storage::disk('local');
        $isMarker = function (string $path, int $x, int $y) use ($disk) {
            $im = imagecreatefromstring($disk->get($path));
            $c  = imagecolorsforindex($im, imagecolorat($im, $x, $y));
            return $c['red'] < 40 && $c['green'] < 40 && $c['blue'] < 40;
        };
        // Marker starts top-left. Front clockwise → top-right; back (long edge)
        // counter-clockwise → bottom-left.
        $this->assertTrue($isMarker("{$dir}/front-bleed12-r-90.jpg", 600, 22));
        $this->assertTrue($isMarker("{$dir}/back-bleed12-r90.jpg", 22, 1050));

        // Short edge: the back is turned clockwise like the front, and the back
        // slots are mirrored top-to-bottom (same set of positions).
        $res = $this->asSession(self::SESSION_A)->get("/print/own-design/business-card/{$id}/pdf/six_up?flip=short")->assertOk();
        $short = $this->parsePdf($res->streamedContent());
        $this->assertSamePositions($this->bledBoxes($expected), $short['pages'][1]['cards']);
        $this->assertTrue($isMarker("{$dir}/back-bleed12-r-90.jpg", 600, 22));
    }

    public function test_png_render_is_accepted_and_normalised_to_jpeg(): void
    {
        $payload = $this->payload(['sides' => ['front' => ['image' => $this->sideImage([10, 200, 10], 2100, 1200, 'png')]]]);
        $id = $this->save($payload)->assertCreated()->json('design_id');

        $bin = Storage::disk('local')->get($this->design($id)->design_data['sides']['front']['image_path']);
        $this->assertSame([1050, 600, IMAGETYPE_JPEG], array_slice(getimagesizefromstring($bin), 0, 3));
    }

    public function test_resave_updates_same_design_and_regenerates_pdfs(): void
    {
        $id   = $this->save($this->payload())->assertCreated()->json('design_id');
        $disk = Storage::disk('local');
        $path = $this->design($id)->design_data['pdfs']['a4_centered']['path'];
        $before = $disk->get($path);

        $this->save($this->payload([
            'design_id' => $id,
            'sides'     => ['back' => ['design' => ['objects' => [['type' => 'textbox', 'text' => 'NEW BACK']]]]],
        ]))->assertOk()->assertJsonPath('design_id', $id);

        $this->assertSame(1, UserDesign::count());
        $data = $this->design($id)->design_data;
        $this->assertSame('NEW BACK', json_decode($disk->get($data['sides']['back']['design_path']), true)['objects'][0]['text']);
        $this->assertSame('FRONT NAME', json_decode($disk->get($data['sides']['front']['design_path']), true)['objects'][0]['text']);
        $this->assertTrue($disk->exists($path));
        $this->assertNotSame(md5($before), md5($disk->get($path)), 'PDF rebuilt for new artwork');
    }

    public function test_existing_pdfs_are_not_regenerated_unnecessarily(): void
    {
        $id     = $this->save($this->payload())->assertCreated()->json('design_id');
        $design = $this->design($id);
        $path   = $design->design_data['pdfs']['letter_10_up']['path'];
        Storage::disk('local')->put($path, '%PDF-sentinel');

        app(BusinessCardPdfService::class)->generate($design, 'letter_10_up');
        $this->assertSame('%PDF-sentinel', Storage::disk('local')->get($path));

        app(BusinessCardPdfService::class)->generate($design, 'letter_10_up', force: true);
        $this->assertNotSame('%PDF-sentinel', Storage::disk('local')->get($path));
    }

    public function test_pdf_failure_keeps_the_saved_design(): void
    {
        $this->app->bind(BusinessCardPdfService::class, fn() => new class extends BusinessCardPdfService {
            public function generate(UserDesign $design, string $format, bool $force = false, array $options = []): string
            {
                throw new \RuntimeException('dompdf exploded');
            }
        });

        $res = $this->save($this->payload())->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('pdfs', [])
            ->assertJsonStructure(['pdf_errors' => ['letter_10_up', 'a4_centered']]);

        $design = $this->design($res->json('design_id'));
        $this->assertTrue(Storage::disk('local')->exists($design->design_data['sides']['front']['image_path']));
        $this->assertTrue(Storage::disk('local')->exists($design->design_data['sides']['back']['image_path']));
        $this->assertArrayHasKey('a4_centered', $design->design_data['pdf_errors']);
    }

    /* ── Validation ──────────────────────────────────────────── */

    public function test_rejects_wrong_dimensions_and_orientation(): void
    {
        $this->save($this->payload(['width_in' => 3, 'height_in' => 5, 'orientation' => 'portrait']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['width_in', 'height_in', 'orientation']);

        // Swapped (portrait) numbers are not accepted either.
        $this->save($this->payload(['width_in' => 2, 'height_in' => 3.5]))
            ->assertUnprocessable()->assertJsonValidationErrors(['width_in', 'height_in']);

        $this->assertSame(0, UserDesign::count());
    }

    public function test_requires_both_sides(): void
    {
        $payload = $this->payload();
        unset($payload['sides']['back']);
        $this->save($payload)->assertUnprocessable()->assertJsonValidationErrors(['sides.back']);

        $payload = $this->payload();
        $payload['sides']['middle'] = $payload['sides']['front'];
        $this->save($payload)->assertUnprocessable()->assertJsonValidationErrors(['sides']);

        $this->assertSame(0, UserDesign::count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_rejects_invalid_side_images(): void
    {
        $cases = [
            'not a data uri'  => 'https://example.com/card.jpg',
            'not an image'    => 'data:image/png;base64,' . base64_encode('<?php echo 1; ?>'),
            'gif'             => 'data:image/gif;base64,' . base64_encode(base64_decode('R0lGODlhAQABAAAAACw=')),
            'portrait render' => $this->sideImage([1, 2, 3], 600, 1050),
            'wrong aspect'    => $this->sideImage([1, 2, 3], 1050, 700),
            'too small'       => $this->sideImage([1, 2, 3], 350, 200),
        ];

        foreach ($cases as $label => $image) {
            $this->save($this->payload(['sides' => ['back' => ['image' => $image]]]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['sides.back.image'], 'errors');
        }

        $this->assertSame(0, UserDesign::count(), 'no partial design saved');
        $this->assertSame([], Storage::disk('local')->allFiles(), 'no files written');
    }

    public function test_rejects_unsafe_file_references_in_design_data(): void
    {
        foreach (['file:///etc/passwd', '../../.env', 'C:\\xampp\\php\\php.ini', 'javascript:alert(1)', 'http://evil.test/../../x.png'] as $src) {
            $this->save($this->payload(['sides' => ['front' => ['design' => ['objects' => [['type' => 'image', 'src' => $src]]]]]]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['sides.front.design']);
        }

        // Inline images and http(s) URLs (uploads, QR codes) are fine.
        $this->save($this->payload(['sides' => ['front' => ['design' => ['objects' => [
            ['type' => 'image', 'src' => 'data:image/png;base64,iVBORw0KGgo='],
            ['type' => 'image', 'src' => 'http://localhost/qrinto/storage/customer-uploads/a.jpg'],
        ]]]]]))->assertCreated();
    }

    public function test_rejects_unknown_product_and_missing_design_objects(): void
    {
        $this->save($this->payload(['product_id' => 999]))
            ->assertUnprocessable()->assertJsonValidationErrors(['product_id']);

        $payload = $this->payload();
        $payload['sides']['front']['design'] = ['background' => '#fff'];
        $this->save($payload)->assertUnprocessable()->assertJsonValidationErrors(['sides.front.design.objects']);
    }

    /* ── Access control ──────────────────────────────────────── */

    public function test_owner_session_can_download_both_pdfs(): void
    {
        $id = $this->save($this->payload())->assertCreated()->json('design_id');

        foreach (BusinessCard::FORMATS as $format => $spec) {
            $res = $this->asSession(self::SESSION_A)->get("/print/own-design/business-card/{$id}/pdf/{$format}");
            $res->assertOk()->assertHeader('content-type', 'application/pdf');
            $this->assertStringContainsString(BusinessCard::variantFilename($format, BusinessCard::printOptions([], $format)), $res->headers->get('content-disposition'));
            $this->assertStringStartsWith('%PDF', $res->streamedContent());
        }
    }

    public function test_other_visitors_cannot_download_or_overwrite(): void
    {
        $id = $this->save($this->payload())->assertCreated()->json('design_id');

        $this->asSession(self::SESSION_B)
            ->get("/print/own-design/business-card/{$id}/pdf/a4_centered")
            ->assertForbidden();

        $this->save($this->payload(['design_id' => $id]), self::SESSION_B)->assertForbidden();
    }

    public function test_signed_in_owner_is_matched_by_user_not_session(): void
    {
        $owner = (new \App\Models\User())->forceFill(['id' => 7]);
        $other = (new \App\Models\User())->forceFill(['id' => 8]);

        $id = $this->actingAs($owner)->save($this->payload())->assertCreated()->json('design_id');
        $this->assertSame(7, (int) $this->design($id)->user_id);

        $url = "/print/own-design/business-card/{$id}/pdf/letter_10_up";
        $this->actingAs($owner)->asSession(self::SESSION_B)->get($url)->assertOk();
        $this->actingAs($other)->asSession(self::SESSION_A)->get($url)->assertForbidden();
    }

    public function test_download_rejects_unknown_format_and_non_business_card_designs(): void
    {
        $id = $this->save($this->payload())->assertCreated()->json('design_id');

        $this->asSession(self::SESSION_A)->get("/print/own-design/business-card/{$id}/pdf/unknown_format")->assertNotFound();
        $this->asSession(self::SESSION_A)->get('/print/own-design/business-card/999/pdf/a4_centered')->assertNotFound();

        $generic = UserDesign::create(['session_id' => self::SESSION_A, 'product_id' => 1, 'design_data' => ['file_path' => 'designs/x.json']]);
        $this->asSession(self::SESSION_A)
            ->get("/print/own-design/business-card/{$generic->id}/pdf/a4_centered")
            ->assertNotFound();
        $this->save($this->payload(['design_id' => $generic->id]))
            ->assertUnprocessable()->assertJsonValidationErrors(['design_id']);
    }

    /* ── Preview / print page ────────────────────────────────── */

    private const PREVIEW = '/print/own-design/sizes/customizer/preview/business-card';

    public function test_save_points_to_the_dedicated_preview_page(): void
    {
        $res = $this->save($this->payload())->assertCreated();
        $id  = $res->json('design_id');

        $this->assertSame(url(self::PREVIEW) . '?design=' . $id, $res->json('preview_url'));
        $this->assertSame('/print/own-design/sizes/customizer/preview/business-card',
            parse_url(route('print.own-design.customizer.preview.business-card'), PHP_URL_PATH));
    }

    public function test_preview_page_offers_7x10_and_letter_sheets(): void
    {
        $id = $this->save($this->payload())->assertCreated()->json('design_id');

        $res = $this->asSession(self::SESSION_A)->get(self::PREVIEW . '?design=' . $id)->assertOk();
        $view = $res->viewData('businessCard');

        // 7 × 10: 10 edge to edge or 6; 8.5 × 11: 12 sideways or 10 upright with a
        // chosen gap. A4 is not offered here (still generated/downloadable).
        $this->assertSame('letter_12_up', $view['defaultFormat']);
        $this->assertSame(['seven_ten_10_up', 'six_up', 'letter_12_up', 'letter_10_up', 'a4_12_up', 'a4_10_up'], array_keys($view['formats']));
        $this->assertSame(['7x10', '7x10', 'letter', 'letter', 'a4', 'a4'], array_column($view['formats'], 'paper'));

        // A4 is narrower: 4 sideways cards only fit with a 1/16 in gap.
        $a4 = $view['formats']['a4_12_up'];
        $this->assertSame(['12 per sheet', 12, [1], 'A4 (8.27 × 11.69 in)'], [$a4['title'], $a4['cards'], array_keys($a4['gaps']), $a4['sheet_name']]);
        $this->assertSame([1, 2, 3], array_keys($view['formats']['a4_10_up']['gaps']));
        $this->assertStringEndsWith("/business-card/{$id}/side/front?bleed=1", $view['sides']['front']);
        $this->assertStringEndsWith("/business-card/{$id}/side/back?bleed=1", $view['sides']['back']);
        $this->assertStringEndsWith("/business-card/{$id}/pdf/letter_12_up", $res->viewData('pdfUrl'));

        $e2e = $view['formats']['seven_ten_10_up'];
        $this->assertSame(['10 per sheet', 10, true, true, false], [$e2e['title'], $e2e['cards'], $e2e['edge_to_edge'], $e2e['recommended'], $e2e['rotated']]);
        $this->assertCount(5, array_merge($e2e['cuts_x'], $e2e['cuts_y']), 'one down the middle, four across');
        $this->assertArrayNotHasKey('gaps', $e2e);

        $twelve = $view['formats']['letter_12_up'];
        $this->assertSame(['12 per sheet', 12, true, 2, [1, 2]], [$twelve['title'], $twelve['cards'], $twelve['rotated'], $twelve['gap_default'], array_keys($twelve['gaps'])]);
        $this->assertSame([0.063, 0.125], $twelve['margin_in']);

        $letter = $view['formats']['letter_10_up'];
        $this->assertSame(['8.5 × 11 inches — 10 per sheet', 612.0, 792.0, 10], [$letter['label'], $letter['width_pt'], $letter['height_pt'], $letter['cards']]);
        $this->assertSame(['10 per sheet', '2 × 5, cards upright', '8.5 × 11 in', false],
            [$letter['title'], $letter['summary'], $letter['sheet_name'], $letter['rotated']]);
        $this->assertFalse($letter['edge_to_edge']);
        $this->assertFalse($letter['recommended']);
        $this->assertSame([1, 2, 3], array_keys($letter['gaps']));
        $this->assertSame([0.188, 0.188], $letter['gaps'][3]['gutter_in']);

        $six = $view['formats']['six_up'];
        $this->assertSame(['6 per sheet', '3 × 2 with gaps · easier to cut by hand', 504.0, 720.0, 6, true, false],
            [$six['title'], $six['summary'], $six['width_pt'], $six['height_pt'], $six['cards'], $six['rotated'], $six['edge_to_edge']]);
        $this->assertStringEndsWith("/business-card/{$id}/pdf/six_up", $six['url']);
        // Upright 2 × 3.5 in cells: first at 0.25 in / 1.375 in from the corner.
        $this->assertEqualsWithDelta(['left' => 0.25 / 7 * 100, 'top' => 1.375 / 10 * 100, 'width' => 2 / 7 * 100, 'height' => 3.5 / 10 * 100, 'n' => 1], $six['front'][0], 1e-3);
        // Both edges of every gap are cut: 6 down, 4 across.
        $this->assertCount(6, $six['cuts_x']);
        $this->assertCount(4, $six['cuts_y']);

        // Cells come from the PDF layout: 8.5×11 grid centred with 1/8 in gutters
        // → (8.5 - 7.125) / 2 = 0.6875 in across, (11 - 10.5) / 2 = 0.25 in down.
        $this->assertEqualsWithDelta(['left' => 0.6875 / 8.5 * 100, 'top' => 0.25 / 11 * 100, 'width' => 3.5 / 8.5 * 100, 'height' => 2 / 11 * 100, 'n' => 1], $letter['front'][0], 1e-3);

        // Cut lines: both edges of every gap → 4 down + 10 across.
        $this->assertCount(4, $letter['cuts_x']);
        $this->assertCount(10, $letter['cuts_y']);

        // Back cells: same positions as the fronts, numbered by the front card
        // behind them - columns swapped for long edge, rows for short edge.
        $pos = fn($cells) => collect($cells)->map(fn($c) => [$c['left'], $c['top']])->sort()->values()->all();
        foreach (['back', 'back_short'] as $key) {
            $this->assertEquals($pos($letter['front']), $pos($letter[$key]));
        }
        $at = fn($cells, $l, $t) => collect($cells)->first(fn($c) => abs($c['left'] - $l) < 0.01 && abs($c['top'] - $t) < 0.01)['n'];
        $tlX = 0.6875 / 8.5 * 100;
        $tlY = 0.25 / 11 * 100;
        $this->assertSame(2, $at($letter['back'], $tlX, $tlY), 'long edge: card 2 is behind top-left');
        $this->assertSame(9, $at($letter['back_short'], $tlX, $tlY), 'short edge: card 9 is behind top-left');

        $res->assertDontSee('A4 · 10 per sheet')->assertDontSee('centered with even gaps');
        $sixAt = fn($cells, $i) => collect($cells)->sortBy(['top', 'left'])->values()[$i]['n'];
        $this->assertSame(3, $sixAt($six['back'], 0), 'long edge: card 3 is behind the top-left back slot');
        $this->assertSame(4, $sixAt($six['back_short'], 0), 'short edge: card 4 is behind the top-left back slot');

        $res->assertSeeInOrder(['Sheet layout', 'Front and back', 'Turn the sheet', 'Check alignment',
                'Paper', 'Space between cards', 'Cards per sheet', 'How the printer turns the sheet', 'Fine-tune back position',
                'Print an alignment test sheet', 'Cut lines', 'Card numbers', 'Sheets', 'Continue to print'])
            ->assertSee('Back to design')
            ->assertDontSee('Check each panel')
            ->assertDontSee('bizcard-result', false);
    }

    public function test_print_variants_follow_flip_and_nudge_and_are_validated(): void
    {
        $id   = $this->save($this->payload())->assertCreated()->json('design_id');
        $base = "/print/own-design/business-card/{$id}/pdf/letter_10_up";
        $mm   = 72 / 25.4;

        // Long edge, backs moved +1.5 mm right / -0.5 mm (up).
        $res = $this->asSession(self::SESSION_A)->get($base . '?dx=1.5&dy=-0.5')->assertOk();
        $this->assertStringContainsString('business-card-8.5x11-10-up-gap-1-8in-bleed-1mm-dx+1.5-dy-0.5.pdf', $res->headers->get('content-disposition'));
        $pdf = $this->parsePdf($res->streamedContent());
        $this->assertSame([0.0, 0.0, 612.0, 792.0], $pdf['pages'][1]['box']);
        $front = collect($pdf['pages'][0]['cards'])->map(fn($c) => [$c['x'], $c['y']])->sort()->values();
        $back  = collect($pdf['pages'][1]['cards'])->map(fn($c) => [round($c['x'] - 1.5 * $mm, 2), round($c['y'] - 0.5 * $mm, 2)])->sort()->values();
        // PDF y grows upward, so moving the back DOWN the page by -0.5 mm raises PDF y by 0.5 mm.
        $this->assertEqualsWithDelta($front->all(), $back->all(), 0.01, 'every back moved by the same nudge');

        // Short edge: separate cached file, back art turned 180°, positions still match.
        $res = $this->asSession(self::SESSION_A)->get($base . '?flip=short')->assertOk();
        $this->assertStringContainsString('business-card-8.5x11-10-up-gap-1-8in-bleed-1mm-short-edge.pdf', $res->headers->get('content-disposition'));
        $pdf = $this->parsePdf($res->streamedContent());
        $this->assertSamePositions($pdf['pages'][0]['cards'], $pdf['pages'][1]['cards']);
        $dir = 'business-cards/' . $this->design($id)->design_data['token'];

        // Alignment test sheet: outlines only, no artwork images.
        $res = $this->asSession(self::SESSION_A)->get($base . '?test=1')->assertOk();
        $pdf = $this->parsePdf($res->streamedContent());
        $this->assertCount(2, $pdf['pages']);
        $this->assertSame([], $pdf['images']);

        // Default variant is unchanged and still the recorded one.
        $this->assertSame("{$dir}/business-card-8.5x11-10-up-gap-1-8in-bleed-1mm.pdf", $this->design($id)->design_data['pdfs']['letter_10_up']['path']);

        // A wider gap is its own file; the cards move apart but stay 10 per side.
        $res = $this->asSession(self::SESSION_A)->get($base . '?gap=3')->assertOk();
        $this->assertStringContainsString('business-card-8.5x11-10-up-gap-3-16in-bleed-1mm.pdf', $res->headers->get('content-disposition'));
        $pdf = $this->parsePdf($res->streamedContent());
        $this->assertCount(10, $pdf['pages'][0]['cards']);
        $xs = collect($pdf['pages'][0]['cards'])->pluck('x')->unique()->sort()->values()->all();
        $this->assertEqualsWithDelta(252 + 13.5, $xs[1] - $xs[0], 0.01, '3/16 in between columns');

        // Out-of-range / unknown values are rejected, never silently used.
        $this->asSession(self::SESSION_A)->getJson($base . '?dx=3.5')->assertUnprocessable()->assertJsonValidationErrors('dx');
        $this->asSession(self::SESSION_A)->getJson($base . '?flip=diagonal')->assertUnprocessable()->assertJsonValidationErrors('flip');
        $this->asSession(self::SESSION_A)->getJson($base . '?test=maybe')->assertUnprocessable()->assertJsonValidationErrors('test');
        // 12 sideways cards don't fit with a 3/16 in gap; 6-up has no gap choice.
        $this->asSession(self::SESSION_A)->getJson("/print/own-design/business-card/{$id}/pdf/letter_12_up?gap=3")->assertUnprocessable()->assertJsonValidationErrors('gap');
        $this->asSession(self::SESSION_A)->getJson("/print/own-design/business-card/{$id}/pdf/six_up?gap=1")->assertUnprocessable()->assertJsonValidationErrors('gap');
    }

    public function test_bleed_mirrors_the_artwork_edges_and_is_limited_by_the_gap(): void
    {
        $id   = $this->save($this->payload())->assertCreated()->json('design_id');
        $base = "/print/own-design/business-card/{$id}/pdf";

        // The preview render: 38 px mirrored a side; the edge column mirrors the one inside it.
        $img = $this->asSession(self::SESSION_A)->get("/print/own-design/business-card/{$id}/side/front?bleed=1")->assertOk();
        $im  = imagecreatefromstring($img->streamedContent());
        $this->assertSame([1126, 676], [imagesx($im), imagesy($im)]);
        $px = fn($x, $y) => array_values(array_slice(imagecolorsforindex($im, imagecolorat($im, $x, $y)), 0, 3));
        $this->assertEqualsWithDelta($px(75, 300), $px(0, 300), 12);
        $this->assertEqualsWithDelta($px(500, 75), $px(500, 0), 12);

        // 1.5 mm on 6-up (1/4 in gaps) -> 18 px; on Letter 12-up with a 1/16 in
        // gap only half the gap fits (2.25 pt -> 9 px); edge to edge gets none.
        $box = fn($url) => $this->parsePdf($this->asSession(self::SESSION_A)->get($url)->assertOk()->streamedContent())['pages'][0]['cards'][0];
        $this->assertEqualsWithDelta(144 + 2 * 4.32, $box("{$base}/six_up?bleed=1.5")['w'], 0.01);
        $this->assertEqualsWithDelta(144 + 2 * 2.16, $box("{$base}/letter_12_up?gap=1&bleed=1.5")['w'], 0.01);
        $this->assertEqualsWithDelta(252.0, $box("{$base}/seven_ten_10_up?bleed=1.5")['w'], 0.01);
        $this->asSession(self::SESSION_A)->getJson("{$base}/six_up?bleed=2")->assertUnprocessable()->assertJsonValidationErrors('bleed');
    }

    public function test_short_edge_sheet_is_the_long_edge_sheet_with_its_back_turned_180(): void
    {
        // The preview prints a short-edge choice as the long-edge variant with the
        // nudge mirrored (the printer is always sent long-edge duplex): turning the
        // short-edge back page 180° must land every card exactly there.
        $id = $this->save($this->payload())->assertCreated()->json('design_id');
        foreach (['letter_10_up' => [612.0, 792.0], 'six_up' => [504.0, 720.0]] as $format => [$w, $h]) {
            $base  = "/print/own-design/business-card/{$id}/pdf/{$format}";
            $short = $this->parsePdf($this->asSession(self::SESSION_A)->get($base . '?flip=short&dx=1.2&dy=-0.4')->assertOk()->streamedContent());
            $long  = $this->parsePdf($this->asSession(self::SESSION_A)->get($base . '?dx=-1.2&dy=0.4')->assertOk()->streamedContent());

            $turned = array_map(fn($c) => ['x' => $w - $c['x'] - $c['w'], 'y' => $h - $c['y'] - $c['h'], 'w' => $c['w'], 'h' => $c['h']], $short['pages'][1]['cards']);
            $key = fn($c) => sprintf('%.2f,%.2f,%.2f,%.2f', $c['x'], $c['y'], $c['w'], $c['h']);
            $a = array_map($key, $turned);
            $b = array_map($key, $long['pages'][1]['cards']);
            sort($a);
            sort($b);
            $this->assertSame($b, $a, "{$format}: back positions");
            $this->assertSamePositions($short['pages'][0]['cards'], $long['pages'][0]['cards']);
        }
    }

    public function test_resave_clears_every_sheet_variant(): void
    {
        $id   = $this->save($this->payload())->assertCreated()->json('design_id');
        $dir  = 'business-cards/' . $this->design($id)->design_data['token'];
        $this->asSession(self::SESSION_A)->get("/print/own-design/business-card/{$id}/pdf/a4_centered?flip=short&dx=1")->assertOk();
        $this->assertTrue(Storage::disk('local')->exists("{$dir}/business-card-a4-centered-bleed-1mm-short-edge-dx+1.0-dy+0.0.pdf"));

        $this->save($this->payload(['design_id' => $id]))->assertOk();
        $this->assertFalse(Storage::disk('local')->exists("{$dir}/business-card-a4-centered-bleed-1mm-short-edge-dx+1.0-dy+0.0.pdf"));
        $this->assertFalse(Storage::disk('local')->exists("{$dir}/back-bleed12-r180.jpg"));
    }

    public function test_preview_page_redirects_to_the_editor_without_a_design(): void
    {
        $this->asSession(self::SESSION_B)->get(self::PREVIEW)
            ->assertRedirect(url('/print/own-design/sizes/customizer') . '?size=3.5x2&orientation=landscape&title=business-card');
    }

    public function test_preview_page_falls_back_to_the_last_design_saved_in_the_session(): void
    {
        $id = $this->save($this->payload())->assertCreated()->json('design_id');
        $this->asSession(self::SESSION_A)->get(self::PREVIEW)->assertOk()
            ->assertViewHas('businessCard', fn($bc) => $bc['designId'] === $id);
    }

    public function test_guest_card_is_claimed_by_user_on_login(): void
    {
        $id = $this->save($this->payload())->assertCreated()->json('design_id');
        $this->assertNull(UserDesign::find($id)->user_id);

        // Login fires before the session ID is regenerated.
        session()->setId(self::SESSION_A);
        session()->start();
        event(new \Illuminate\Auth\Events\Login('web', \App\Models\User::find(8), false));

        $this->assertSame(8, (int) UserDesign::find($id)->user_id);
        // Afterwards the user reaches it from any session.
        $this->actingAs(\App\Models\User::find(8))->asSession(self::SESSION_B)
            ->get("/print/own-design/business-card/{$id}/side/front")->assertOk();
    }

    public function test_preview_page_and_side_images_are_owner_only(): void
    {
        $id = $this->save($this->payload())->assertCreated()->json('design_id');

        $this->asSession(self::SESSION_B)->get(self::PREVIEW . '?design=' . $id)->assertForbidden();
        $this->asSession(self::SESSION_B)->get("/print/own-design/business-card/{$id}/side/front")->assertForbidden();
        $this->asSession(self::SESSION_A)->get(self::PREVIEW . '?design=999')->assertNotFound();

        $img = $this->asSession(self::SESSION_A)->get("/print/own-design/business-card/{$id}/side/back")->assertOk();
        $this->assertSame('image/jpeg', $img->headers->get('content-type'));
        $this->assertSame([1050, 600], array_slice(getimagesizefromstring($img->streamedContent()), 0, 2));
        $this->asSession(self::SESSION_A)->get("/print/own-design/business-card/{$id}/side/inside")->assertNotFound();
    }

    public function test_preview_generates_nothing_and_print_fetch_builds_only_the_selected_pdf(): void
    {
        $id     = $this->save($this->payload())->assertCreated()->json('design_id');
        $disk   = Storage::disk('local');
        $design = $this->design($id);
        foreach ($design->design_data['pdfs'] as $pdf) {
            $disk->delete($pdf['path']);
        }
        $dir = 'business-cards/' . $design->design_data['token'];

        $this->asSession(self::SESSION_A)->get(self::PREVIEW . '?design=' . $id)->assertOk();
        $this->assertFalse($disk->exists("{$dir}/business-card-a4-centered.pdf"));
        $this->assertFalse($disk->exists("{$dir}/business-card-8.5x11-12-up-gap-1-8in-bleed-1mm.pdf"));

        // Print fetches the page's default (8.5×11, 12-up) PDF URL.
        $pdf = $this->asSession(self::SESSION_A)->get("/print/own-design/business-card/{$id}/pdf/letter_12_up")->assertOk();
        $this->assertSame([0.0, 0.0, 612.0, 792.0], $this->parsePdf($pdf->streamedContent())['pages'][0]['box']);
        $this->assertTrue($disk->exists("{$dir}/business-card-8.5x11-12-up-gap-1-8in-bleed-1mm.pdf"));
        $this->assertFalse($disk->exists("{$dir}/business-card-a4-centered.pdf"), 'A4 not generated by printing');
    }

    /* ── Routes ──────────────────────────────────────────────── */

    public function test_routes_are_registered_as_specified(): void
    {
        $this->assertSame(url('/print/own-design/business-card/save'), route('business-card.save'));
        $route = app('router')->getRoutes()->getByName('business-card.save');
        $this->assertSame(['POST'], $route->methods());
        $this->assertSame('App\Http\Controllers\BusinessCardController@save', $route->getActionName());
        $this->assertContains('web', $route->gatherMiddleware(), 'web group → CSRF protection');
    }

    /** Card boxes as printed with the default 1 mm bleed: 12 px at 300 DPI = 2.88 pt a side. */
    private function bledBoxes(array $cards, float $b = 2.88): array
    {
        return array_map(fn($c) => ['x' => $c['x'] - $b, 'y' => $c['y'] - $b, 'w' => $c['w'] + 2 * $b, 'h' => $c['h'] + 2 * $b], $cards);
    }

    private function assertSamePositions(array $a, array $b): void
    {
        $key = fn($c) => sprintf('%.3f,%.3f,%.3f,%.3f', $c['x'], $c['y'], $c['w'], $c['h']);
        $a = array_map($key, $a);
        $b = array_map($key, $b);
        sort($a);
        sort($b);
        $this->assertSame($a, $b, 'front and back sheets use identical card positions');
    }
}
