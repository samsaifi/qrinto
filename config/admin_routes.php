<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Role-Based Navigation Routes Configuration
    |--------------------------------------------------------------------------
    |
    | Defines the sidebar navigation menu items ($topItems, $catalogItems, $bottomItems)
    | for each user role: admin, store_admin, staff.
    |
    | You can manually add, remove, or reorder items for any role below.
    |
    */

    'admin' => [
        'top' => [
            [
                'route' => 'admin.dashboard',
                'icon' => 'layout-dashboard',
                'label' => 'Dashboard',
                'match' => '*.dashboard*',
            ],
            [
                'route' => 'admin.orders.index',
                'icon' => 'package',
                'label' => 'Orders',
                'match' => '*.orders*',
            ],
            // NOTE: "Print Logs" now renders as an accordion (Print Logs + Local
            // Print) directly in layouts/admin.blade.php for the admin role, so it
            // is intentionally omitted here.
        ],
        'catalog' => [
            [
                'route' => 'admin.products.index',
                'icon' => 'box',
                'label' => 'Products',
                'match' => '*.products*',
            ],
            [
                'route' => 'admin.categories.index',
                'icon' => 'grid-2x2',
                'label' => 'Categories',
                'match' => '*.categories*',
            ],
            [
                'route' => 'admin.product-types.index',
                'icon' => 'layers',
                'label' => 'Card Types/Sizes',
                'match' => '*.product-types*',
            ],
            [
                'route' => 'admin.templates.index',
                'icon' => 'layout-template',
                'label' => 'Templates',
                'match' => '*.templates*',
            ],
            [
                'route' => 'admin.coupons.index',
                'icon' => 'tag',
                'label' => 'Coupons',
                'match' => '*.coupons*',
            ],
            [
                'route' => 'admin.events.index',
                'icon' => 'calendar',
                'label' => 'Events',
                'match' => '*.events*',
            ],
           
        ],
        'bottom' => [
            [
                'route' => 'admin.stores.index',
                'icon' => 'store',
                'label' => 'Stores',
                'match' => '*.stores*',
            ],
            [
                'route' => 'admin.users.index',
                'icon' => 'users',
                'label' => 'Users',
                'match' => '*.users*',
            ],
        ],
    ],

    // Store panel is a work queue, not an analytics dashboard. Catalog
    // administration (products, categories, templates, coupons, events, paper
    // types) belongs to the NAC admin panel above - not the store nav.
    'store_admin' => [
        'top' => [
            [
                'route' => 'store.orders.index',
                'icon' => 'package',
                'label' => 'Orders',
                'match' => '*.orders*',
            ],
            [
                'route' => 'storepanel.qr',
                'icon' => 'qr-code',
                'label' => 'Store QR',
                'match' => 'storepanel.qr*',
            ],
            [
                'route' => 'store.trays',
                'icon' => 'layers',
                'label' => 'Trays',
                'match' => '*.trays*',
            ],
        ],
        'catalog' => [],
        'bottom' => [],
    ],

    'staff' => [
        'top' => [
            [
                'route' => 'staff.orders.index',
                'icon' => 'package',
                'label' => 'Orders',
                'match' => '*.orders*',
            ],
            [
                'route' => 'storepanel.qr',
                'icon' => 'qr-code',
                'label' => 'Store QR',
                'match' => 'storepanel.qr*',
            ],
            [
                'route' => 'store.trays',
                'icon' => 'layers',
                'label' => 'Trays',
                'match' => '*.trays*',
            ],
        ],
        'catalog' => [],
        'bottom' => [],
    ],
];
