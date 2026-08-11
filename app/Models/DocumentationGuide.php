<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class DocumentationGuide extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'category',
        'subcategory',
        'icon',
        'summary',
        'content',
        'visible_roles',
        'is_published',
        'sort_order',
        'views_count',
    ];

    protected $casts = [
        'visible_roles' => 'array',
        'is_published' => 'boolean',
        'sort_order' => 'integer',
        'views_count' => 'integer',
    ];

    /**
     * Check if a user with a given role can view this guide
     */
    public function isVisibleToRole(?string $role): bool
    {
        if (empty($this->visible_roles)) {
            return true;
        }

        if ($role === 'admin') {
            return true;
        }

        return in_array($role, (array)$this->visible_roles, true);
    }

    /**
     * Helper to ensure table exists and default documentation is seeded
     */
    public static function ensureTableAndSeeded(): void
    {
        try {
            if (!Schema::hasTable('documentation_guides')) {
                Schema::create('documentation_guides', function (Blueprint $table) {
                    $table->id();
                    $table->string('title');
                    $table->string('slug')->unique();
                    $table->string('category');
                    $table->string('subcategory')->nullable();
                    $table->string('icon')->default('book-open');
                    $table->text('summary')->nullable();
                    $table->longText('content');
                    $table->json('visible_roles')->nullable();
                    $table->boolean('is_published')->default(true);
                    $table->integer('sort_order')->default(0);
                    $table->integer('views_count')->default(0);
                    $table->timestamps();
                });
            }

            if (self::count() === 0) {
                \App\Services\DocumentationSeederService::seedDefaultGuides();
            }
        } catch (\Throwable $e) {
            \Log::error("DocumentationGuide table initialization error: " . $e->getMessage());
        }
    }
}
