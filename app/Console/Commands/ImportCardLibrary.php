<?php

namespace App\Console\Commands;

use App\Models\Template;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Seeds the card editor's Text styles, Sticker (emoji) sets and Background palettes
 * (resources/card-templates/library.json, exported from public/js/card-editor.js)
 * into the templates table:
 *   text_style  - one row per style; canvas_config = {category, lines: [{text, font, size, color, ...}]}
 *   sticker_set - one row per category; canvas_config = {items: ['💖', ...]}
 *   bg_palette  - one row per theme;    canvas_config = {items: ['#ffffff', ...]}
 */
class ImportCardLibrary extends Command
{
    protected $signature = 'templates:import-library {--skip-existing}';
    protected $description = 'Import card editor text styles, emoji sets and background palettes into the admin templates table';

    private int $created = 0, $updated = 0, $skipped = 0;

    public function handle(): int
    {
        $path = resource_path('card-templates/library.json');
        $data = json_decode((string) @file_get_contents($path), true);
        if (!is_array($data)) {
            $this->error("Cannot read {$path}");
            return self::FAILURE;
        }

        $sort = 0;
        foreach ($data['pairs'] ?? [] as $category => $pairs) {
            foreach ($pairs as $i => $lines) {
                $name = $lines[0]['text'] ?? 'Text style';
                $this->put('text-' . Str::slug($category) . '-' . ($i + 1), Template::KIND_TEXT, $name, 'type', [
                    'category' => $category,
                    'lines'    => $lines,
                ], $sort++);
            }
        }
        $i = 0;
        foreach ($data['stickers'] ?? [] as $category => $items) {
            $this->put('emoji-' . Str::slug($category), Template::KIND_STICKER, $category, 'smile', ['items' => $items], $i++);
        }
        $i = 0;
        foreach ($data['bgColors'] ?? [] as $theme => $items) {
            $this->put('bg-' . Str::slug($theme), Template::KIND_BG, $theme, 'palette', ['items' => $items], $i++);
        }

        $this->info("Library: {$this->created} created, {$this->updated} updated, {$this->skipped} skipped.");
        return self::SUCCESS;
    }

    private function put(string $slug, string $kind, string $name, string $icon, array $config, int $sort): void
    {
        $existing = Template::where('slug', $slug)->first();
        if ($existing && $this->option('skip-existing')) {
            $this->skipped++;
            return;
        }
        $values = [
            'name' => $name, 'kind' => $kind, 'icon_type' => 'lucide', 'icon_value' => $icon,
            'canvas_config' => $config, 'is_active' => true, 'sort_order' => $sort,
        ];
        if ($existing) {
            $existing->update($values);
            $this->updated++;
        } else {
            Template::create($values + ['slug' => $slug]);
            $this->created++;
        }
    }
}
