<?php

namespace App\Console\Commands;

use App\Models\Template;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Seeds the SVG artwork in public/assets/templates/<group>/*.svg into the templates table
 * as kind=svg_art. canvas_config = {group, src (relative to assets/templates/), x, y, w, h}:
 * the default place and size on a 360 x 504 page when the art is added to a card.
 */
class ImportCardSvgs extends Command
{
    protected $signature = 'templates:import-svgs {--skip-existing}';
    protected $description = 'Import the card template SVG artwork into the admin templates table';

    public function handle(): int
    {
        $base = public_path('assets/templates');
        $created = $updated = $skipped = 0;
        $groups = collect(glob($base . '/*', GLOB_ONLYDIR))->map('basename')->sort()->values();

        foreach ($groups as $g => $group) {
            foreach (glob("$base/$group/*.svg") as $i => $file) {
                $name = Str::title(str_replace(['-', '_'], ' ', pathinfo($file, PATHINFO_FILENAME)));
                $slug = 'svg-' . $group . '-' . Str::slug($name);
                $existing = Template::where('slug', $slug)->first();
                if ($existing && $this->option('skip-existing')) {
                    $skipped++;
                    continue;
                }
                [$w, $h] = $this->fitBox($file, 140);
                $values = [
                    'name'          => $name,
                    'kind'          => Template::KIND_SVG,
                    'icon_type'     => 'lucide',
                    'icon_value'    => 'shapes',
                    'canvas_config' => [
                        'group' => Str::title(str_replace('-', ' ', $group)),
                        'src'   => $group . '/' . basename($file),
                        'x'     => (int) round((360 - $w) / 2),
                        'y'     => (int) round((504 - $h) / 2),
                        'w'     => $w,
                        'h'     => $h,
                    ],
                    'is_active'     => true,
                    'sort_order'    => $g * 100 + $i,
                ];
                if ($existing) {
                    $existing->update($values);
                    $updated++;
                } else {
                    Template::create($values + ['slug' => $slug]);
                    $created++;
                }
            }
        }

        $this->info("SVGs: {$created} created, {$updated} updated, {$skipped} skipped.");
        return self::SUCCESS;
    }

    /** Size the SVG (from its viewBox / width / height) so its longer side is $max px. */
    private function fitBox(string $file, int $max): array
    {
        $head = (string) file_get_contents($file, false, null, 0, 2000);
        $w = $h = 100;
        if (preg_match('/viewBox="\s*[-\d.]+[\s,]+[-\d.]+[\s,]+([\d.]+)[\s,]+([\d.]+)/i', $head, $m)) {
            [$w, $h] = [(float) $m[1], (float) $m[2]];
        } elseif (preg_match('/width="([\d.]+)".*?height="([\d.]+)"/is', $head, $m)) {
            [$w, $h] = [(float) $m[1], (float) $m[2]];
        }
        $k = $max / max($w, $h, 1);
        return [max(10, (int) round($w * $k)), max(10, (int) round($h * $k))];
    }
}
