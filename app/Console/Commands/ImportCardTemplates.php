<?php

namespace App\Console\Commands;

use App\Models\Template;
use Illuminate\Console\Command;

/**
 * Copies the static greeting-card library (public/assets/templates/templates.json)
 * into the templates table as kind=card rows, so admins can manage them.
 * Existing rows (matched by slug) are updated unless --skip-existing is given.
 */
class ImportCardTemplates extends Command
{
    protected $signature = 'templates:import-cards {--skip-existing}';
    protected $description = 'Import card editor templates from templates.json into the admin templates table';

    public function handle(): int
    {
        $path = public_path('assets/templates/templates.json');
        $data = json_decode((string) @file_get_contents($path), true);
        if (!is_array($data) || empty($data['templates'])) {
            $this->error("No templates found in {$path}");
            return self::FAILURE;
        }

        $categories = collect($data['categories'] ?? [])->pluck('name', 'id');
        $created = $updated = $skipped = 0;

        foreach (array_values($data['templates']) as $i => $t) {
            $existing = Template::where('slug', $t['id'])->first();
            if ($existing && $this->option('skip-existing')) {
                $skipped++;
                continue;
            }

            $values = [
                'name'          => $t['name'],
                'kind'          => Template::KIND_CARD,
                'icon_type'     => 'lucide',
                'icon_value'    => 'layout-template',
                'canvas_config' => [
                    'category'      => $t['category'] ?? 'other',
                    'category_name' => $categories[$t['category'] ?? ''] ?? ucfirst(str_replace('-', ' ', $t['category'] ?? 'Other')),
                    'style'         => $t['style'] ?? '',
                    'tags'          => $t['tags'] ?? [],
                    'featured'      => (bool) ($t['featured'] ?? false),
                    'popular'       => (int) ($t['popular'] ?? 0),
                    'pages'         => $t['pages'],
                ],
                'is_active'     => true,
                'sort_order'    => $i,
            ];

            if ($existing) {
                $existing->update($values);
                $updated++;
            } else {
                Template::create($values + ['slug' => $t['id']]);
                $created++;
            }
        }

        $this->info("Card templates: {$created} created, {$updated} updated, {$skipped} skipped.");
        return self::SUCCESS;
    }
}
