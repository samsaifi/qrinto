<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Classic Birthday (Folded 5x7)',
                'category' => 'Birthday',
                'type' => 'folded_card',
                'price' => 4.99,
                'is_active' => true,
                'preview_image_url' => 'https://placehold.co/600x400/orange/white?text=Happy+Birthday',
                'print_specs' => [
                    'width_mm' => 127,
                    'height_mm' => 178,
                    'bleed_mm' => 3,
                    'dpi' => 300
                ],
                'structure' => [
                    'pages' => [
                        [
                            'name' => 'Front',
                            'width' => 1500, // pixels at 300dpi approx
                            'height' => 2100,
                            'slots' => [
                                [
                                    'id' => 'main_photo',
                                    'type' => 'image',
                                    'x' => 100,
                                    'y' => 100,
                                    'width' => 1300,
                                    'height' => 1300,
                                    'label' => 'Upload Photo'
                                ],
                                [
                                    'id' => 'greeting',
                                    'type' => 'text',
                                    'x' => 100,
                                    'y' => 1600,
                                    'width' => 1300,
                                    'height' => 200,
                                    'default' => 'Happy Birthday!',
                                    'max_length' => 50
                                ]
                            ]
                        ],
                        [
                            'name' => 'Inside',
                            'width' => 1500,
                            'height' => 2100,
                            'slots' => [
                                [
                                    'id' => 'message',
                                    'type' => 'text',
                                    'x' => 100,
                                    'y' => 500,
                                    'width' => 1300,
                                    'height' => 1000,
                                    'default' => 'Wishing you the best day ever!',
                                    'multiline' => true
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                'name' => 'Simple Thank You (Postcard 4x6)',
                'category' => 'Thank You',
                'type' => 'postcard',
                'price' => 1.99,
                'is_active' => true,
                'preview_image_url' => 'https://placehold.co/600x400/blue/white?text=Thank+You',
                'print_specs' => [
                    'width_mm' => 102,
                    'height_mm' => 152,
                    'bleed_mm' => 3,
                    'dpi' => 300
                ],
                'structure' => [
                    'pages' => [
                        [
                            'name' => 'Front',
                            'width' => 1800,
                            'height' => 1200,
                            'slots' => [
                                [
                                    'id' => 'full_photo',
                                    'type' => 'image',
                                    'x' => 0,
                                    'y' => 0,
                                    'width' => 1800,
                                    'height' => 1200,
                                    'label' => 'Full Photo'
                                ],
                                [
                                    'id' => 'overlay_text',
                                    'type' => 'text',
                                    'x' => 100,
                                    'y' => 1000,
                                    'width' => 1600,
                                    'height' => 150,
                                    'default' => 'Thank You',
                                    'style' => ['color' => '#FFFFFF', 'shadow' => true]
                                ]
                            ]
                        ],
                        [
                            'name' => 'Back',
                            'width' => 1800,
                            'height' => 1200,
                            'slots' => [] // Standard postcard back
                        ]
                    ]
                ]
            ]
        ];

        foreach ($templates as $template) {
            Template::create($template);
        }
    }
}
