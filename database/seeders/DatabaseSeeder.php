<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductOptionGroup;
use App\Models\ProductOptionValue;
use App\Models\Coupon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin User
        User::create([
            'name' => 'Admin',
            'email' => 'admin@qrinto.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '9876543210',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // 2. Test Customer
        User::create([
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'phone' => '9876543211',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // 3. Categories
        $categories = [
            ['name' => 'Acrylic Prints', 'description' => 'Premium UV-printed acrylic wall art with crystal-clear finish and vibrant colors. Perfect for modern interiors.', 'sort_order' => 1],
            ['name' => 'Canvas Prints', 'description' => 'Museum-quality canvas prints stretched on solid wood frames. Timeless wall decor for any space.', 'sort_order' => 2],
            ['name' => 'Poster Prints', 'description' => 'High-quality poster prints on premium paper stock. Affordable art for every room.', 'sort_order' => 3],
            ['name' => 'Framed Photos', 'description' => 'Custom framed photographs with premium frames and museum-grade glass. Gallery-ready wall art.', 'sort_order' => 4],
            ['name' => 'Photo Collages', 'description' => 'Custom multi-photo collages combining your favorite moments into one stunning piece.', 'sort_order' => 5],
            ['name' => 'Metal Prints', 'description' => 'Durable metal prints with a modern look. Vibrant colors infused directly into aluminum.', 'sort_order' => 6],
        ];

        $categoryModels = [];
        foreach ($categories as $cat) {
            $categoryModels[$cat['name']] = Category::create(array_merge($cat, ['is_active' => true]));
        }

        // 4. Products
        $products = [
            [
                'category' => 'Acrylic Prints',
                'name' => 'Classic Acrylic Wall Photo',
                'short_description' => 'Transform your favorite photo into a stunning acrylic wall piece with vivid colors and crystal-clear finish.',
                'description' => "Our Classic Acrylic Wall Photo is the perfect way to showcase your cherished memories. Printed using state-of-the-art UV technology directly onto premium 3mm acrylic glass, your photos come alive with incredible depth and vibrancy.\n\n• Crystal-clear 3mm acrylic glass\n• UV-direct printing for vivid colors\n• Pre-drilled mounting holes included\n• Lightweight and durable\n• Scratch-resistant coating\n• Available in multiple sizes",
                'base_price' => 1299,
                'compare_price' => 1999,
                'sku' => 'ACP-001',
                'is_featured' => true,
                'min_images' => 1,
                'max_images' => 1,
                'meta_title' => 'Custom Acrylic Wall Photo Print | Premium Quality',
                'meta_description' => 'Create custom acrylic wall art from your photos. Crystal-clear finish, vibrant colors. Free shipping on $999+.',
            ],
            [
                'category' => 'Acrylic Prints',
                'name' => 'Acrylic Photo Collage Board',
                'short_description' => 'Create a stunning multi-photo collage on premium acrylic. Perfect for gifting or home decor.',
                'description' => "Combine up to 9 of your favorite photos into one beautiful acrylic art piece. Our collage board features a sleek, modern look that's perfect for any room.\n\n• Up to 9 photos per board\n• Professional layout design\n• Premium 3mm acrylic\n• Ready to hang\n• Available in various grid layouts",
                'base_price' => 1999,
                'compare_price' => 2999,
                'sku' => 'APC-002',
                'is_featured' => true,
                'min_images' => 2,
                'max_images' => 9,
            ],
            [
                'category' => 'Canvas Prints',
                'name' => 'Gallery Canvas Print',
                'short_description' => 'Museum-quality canvas print stretched on solid wood frame. Rich textures and true-to-life colors.',
                'description' => "Turn your photos into gallery-worthy art with our premium canvas prints. Each print is made using archival-quality inks on premium cotton-poly blend canvas, stretched over a 1.5\" solid wood frame.\n\n• Premium cotton-poly canvas\n• Archival-quality inks\n• 1.5\" solid wood stretcher bars\n• Mirror-wrapped edges\n• Ready to hang hardware included",
                'base_price' => 999,
                'compare_price' => 1499,
                'sku' => 'CNV-001',
                'is_featured' => true,
                'min_images' => 1,
                'max_images' => 1,
            ],
            [
                'category' => 'Canvas Prints',
                'name' => 'Split Canvas Set',
                'short_description' => 'Your photo split across multiple canvas panels for a dramatic display. A showstopper for any wall.',
                'description' => "Make a bold statement with our split canvas sets. Your single photo is artfully divided across 3 or 5 panels, creating a stunning panoramic display.\n\n• Available in 3 or 5 panel sets\n• Professional split calculation\n• Consistent color across panels\n• Premium canvas material\n• Easy installation guide included",
                'base_price' => 2499,
                'compare_price' => 3499,
                'sku' => 'CNV-002',
                'is_featured' => true,
                'min_images' => 1,
                'max_images' => 1,
            ],
            [
                'category' => 'Poster Prints',
                'name' => 'Premium Poster Print',
                'short_description' => 'Affordable high-quality poster prints on premium matte or glossy paper. Perfect for any space.',
                'description' => "Get stunning poster prints at affordable prices without compromising on quality. Printed on 300gsm premium paper stock with archival inks.\n\n• 300gsm premium paper\n• Matte or glossy finish\n• Archival quality inks\n• Sharp detail reproduction\n• Rolled in protective tube",
                'base_price' => 399,
                'compare_price' => 599,
                'sku' => 'PST-001',
                'is_featured' => false,
                'min_images' => 1,
                'max_images' => 1,
            ],
            [
                'category' => 'Framed Photos',
                'name' => 'Custom Framed Portrait',
                'short_description' => 'Beautifully framed photo prints with premium wooden or metal frames. Gallery-ready art for your walls.',
                'description' => "Elevate your photos with our custom framing service. Choose from premium wood or sleek metal frames, paired with gallery-grade glass for lasting beauty.\n\n• Premium wood or metal frames\n• Museum-grade UV glass\n• Acid-free matting\n• Professional mounting\n• Ready to hang",
                'base_price' => 1799,
                'compare_price' => 2499,
                'sku' => 'FRM-001',
                'is_featured' => false,
                'min_images' => 1,
                'max_images' => 1,
            ],
            [
                'category' => 'Photo Collages',
                'name' => 'Heart-Shape Photo Collage',
                'short_description' => 'Your favorite photos arranged in a beautiful heart shape. Perfect gift for loved ones.',
                'description' => "Express your love with our heart-shaped photo collage. Upload 10-30 photos and we'll arrange them in a beautiful heart formation.\n\n• Unique heart-shaped layout\n• 10-30 photos per collage\n• Available on acrylic, canvas, or poster\n• Multiple size options\n• Professional photo arrangement",
                'base_price' => 1499,
                'compare_price' => 2199,
                'sku' => 'CLG-001',
                'is_featured' => true,
                'min_images' => 10,
                'max_images' => 30,
            ],
            [
                'category' => 'Metal Prints',
                'name' => 'Brushed Aluminum Photo',
                'short_description' => 'Modern metal prints with a stunning brushed aluminum finish. Ultra-durable and weatherproof.',
                'description' => "Give your photos a modern, industrial look with our brushed aluminum prints. Your image is infused directly into the metal for exceptional durability and vivid color.\n\n• Premium brushed aluminum\n• Dye-sublimation printing\n• Weatherproof & UV resistant\n• Float mount included\n• Modern, frameless look",
                'base_price' => 2299,
                'compare_price' => 3299,
                'sku' => 'MTL-001',
                'is_featured' => false,
                'min_images' => 1,
                'max_images' => 1,
            ],
        ];

        foreach ($products as $prodData) {
            $categoryName = $prodData['category'];
            unset($prodData['category']);

            $product = Product::create(array_merge($prodData, [
                'category_id' => $categoryModels[$categoryName]->id,
                'is_active' => true,
                'sort_order' => 0,
            ]));

            // Add option groups to each product
            $this->addStandardOptions($product, $categoryName);
        }

        // 5. Coupons
        Coupon::create([
            'code' => 'WELCOME10',
            'name' => 'Welcome 10% Off',
            'type' => 'percentage',
            'value' => 10,
            'min_order_amount' => 999,
            'usage_limit' => 1000,
            'used_count' => 0,
            'is_active' => true,
            'expires_at' => now()->addMonths(6),
        ]);

        Coupon::create([
            'code' => 'FLAT200',
            'name' => 'Flat $200 Off',
            'type' => 'fixed',
            'value' => 200,
            'min_order_amount' => 1499,
            'usage_limit' => 500,
            'used_count' => 0,
            'is_active' => true,
            'expires_at' => now()->addMonths(3),
        ]);
    }

    private function addStandardOptions(Product $product, string $categoryName): void
    {
        // Size options
        $sizeGroup = ProductOptionGroup::create([
            'product_id' => $product->id,
            'name' => 'Size',
            'slug' => 'size',
            'display_type' => 'buttons',
            'is_required' => true,
            'sort_order' => 1,
        ]);

        $sizes = match($categoryName) {
            'Acrylic Prints', 'Metal Prints' => [
                ['label' => '8×10 inch', 'price_modifier' => 0, 'sort_order' => 1],
                ['label' => '12×16 inch', 'price_modifier' => 300, 'sort_order' => 2],
                ['label' => '16×20 inch', 'price_modifier' => 600, 'sort_order' => 3],
                ['label' => '20×30 inch', 'price_modifier' => 1200, 'sort_order' => 4],
                ['label' => '24×36 inch', 'price_modifier' => 1800, 'sort_order' => 5],
            ],
            'Canvas Prints' => [
                ['label' => '8×10 inch', 'price_modifier' => 0, 'sort_order' => 1],
                ['label' => '11×14 inch', 'price_modifier' => 200, 'sort_order' => 2],
                ['label' => '16×20 inch', 'price_modifier' => 500, 'sort_order' => 3],
                ['label' => '24×30 inch', 'price_modifier' => 900, 'sort_order' => 4],
                ['label' => '30×40 inch', 'price_modifier' => 1500, 'sort_order' => 5],
            ],
            'Poster Prints' => [
                ['label' => 'A4', 'price_modifier' => 0, 'sort_order' => 1],
                ['label' => 'A3', 'price_modifier' => 100, 'sort_order' => 2],
                ['label' => 'A2', 'price_modifier' => 250, 'sort_order' => 3],
                ['label' => 'A1', 'price_modifier' => 500, 'sort_order' => 4],
            ],
            default => [
                ['label' => 'Small', 'price_modifier' => 0, 'sort_order' => 1],
                ['label' => 'Medium', 'price_modifier' => 400, 'sort_order' => 2],
                ['label' => 'Large', 'price_modifier' => 800, 'sort_order' => 3],
                ['label' => 'Extra Large', 'price_modifier' => 1500, 'sort_order' => 4],
            ],
        };

        foreach ($sizes as $size) {
            ProductOptionValue::create(array_merge($size, [
                'option_group_id' => $sizeGroup->id,
                'value' => \Str::slug($size['label']),
                'is_active' => true,
            ]));
        }

        // Material/Finish options
        if (in_array($categoryName, ['Acrylic Prints', 'Poster Prints', 'Metal Prints'])) {
            $finishGroup = ProductOptionGroup::create([
                'product_id' => $product->id,
                'name' => 'Finish',
                'slug' => 'finish',
                'display_type' => 'cards',
                'is_required' => true,
                'sort_order' => 2,
            ]);

            $finishes = match($categoryName) {
                'Acrylic Prints' => [
                    ['label' => 'Glossy', 'description' => 'High shine, vibrant colors', 'price_modifier' => 0],
                    ['label' => 'Matte', 'description' => 'No glare, subtle elegance', 'price_modifier' => 100],
                    ['label' => 'Frosted', 'description' => 'Elegant frosted look', 'price_modifier' => 200],
                ],
                'Poster Prints' => [
                    ['label' => 'Matte', 'description' => 'Classic matte finish', 'price_modifier' => 0],
                    ['label' => 'Glossy', 'description' => 'High-shine glossy', 'price_modifier' => 50],
                    ['label' => 'Satin', 'description' => 'Smooth satin texture', 'price_modifier' => 75],
                ],
                default => [
                    ['label' => 'Standard', 'price_modifier' => 0],
                    ['label' => 'Premium', 'price_modifier' => 300],
                ],
            };

            foreach ($finishes as $i => $finish) {
                ProductOptionValue::create(array_merge($finish, [
                    'option_group_id' => $finishGroup->id,
                    'value' => \Str::slug($finish['label']),
                    'is_active' => true,
                    'sort_order' => $i + 1,
                ]));
            }
        }

        // Frame options for relevant categories
        if (in_array($categoryName, ['Framed Photos', 'Canvas Prints'])) {
            $frameGroup = ProductOptionGroup::create([
                'product_id' => $product->id,
                'name' => 'Frame',
                'slug' => 'frame',
                'display_type' => 'cards',
                'is_required' => $categoryName === 'Framed Photos',
                'sort_order' => 3,
            ]);

            $frames = [
                ['label' => 'Classic Black', 'description' => 'Elegant matte black wood frame', 'price_modifier' => 0],
                ['label' => 'Natural Oak', 'description' => 'Warm natural oak finish', 'price_modifier' => 200],
                ['label' => 'Walnut Brown', 'description' => 'Rich walnut wood frame', 'price_modifier' => 250],
                ['label' => 'White Minimal', 'description' => 'Clean white contemporary frame', 'price_modifier' => 150],
                ['label' => 'Gold Accent', 'description' => 'Premium gold-finished frame', 'price_modifier' => 400],
            ];

            foreach ($frames as $i => $frame) {
                ProductOptionValue::create(array_merge($frame, [
                    'option_group_id' => $frameGroup->id,
                    'value' => \Str::slug($frame['label']),
                    'is_active' => true,
                    'sort_order' => $i + 1,
                ]));
            }
        }
    }
}
