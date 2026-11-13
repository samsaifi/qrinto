<?php

namespace App\Console\Commands;

use App\Models\Template;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Seeds the card editor's built-in layouts (resources/card-templates/layouts.json,
 * exported from public/js/card-editor.js) into the templates table as kind=layout.
 * canvas_config = {view: front|inside|back, pages: {front: [els]} | {inL: [...], inR: [...]} | {back: [...]}}.
 */
class ImportCardLayouts extends Command
{
    protected $signature = 'templates:import-layouts {--skip-existing}';
    protected $description = 'Import card editor layouts from layouts.json into the admin templates table';

    public function handle(): int
    {
        $path = resource_path('card-templates/layouts.json');
        $data = json_decode((string) @file_get_contents($path), true);
        if (!is_array($data)) {
            $this->error("Cannot read {$path}");
            return self::FAILURE;
        }

        $created = $updated = $skipped = 0;
        foreach (['front', 'inside', 'back'] as $view) {
            foreach (array_values($data[$view] ?? []) as $i => $layout) {
                $slug = 'layout-' . $view . '-' . Str::slug($layout['name']);
                $existing = Template::where('slug', $slug)->first();
                if ($existing && $this->option('skip-existing')) {
                    $skipped++;
                    continue;
                }
                $values = [
                    'name'          => $layout['name'],
                    'kind'          => Template::KIND_LAYOUT,
                    'icon_type'     => 'lucide',
                    'icon_value'    => 'layout-dashboard',
                    'canvas_config' => ['view' => $view, 'pages' => $layout['pages']],
                    'is_active'     => true,
                    'sort_order'    => $i,
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

        $this->info("Layouts: {$created} created, {$updated} updated, {$skipped} skipped.");
        return self::SUCCESS;
    }
}
