<?php

namespace App\Services;

use App\Models\DocumentationGuide;

class DocumentationSeederService
{
    public static function seedDefaultGuides(): void
    {
        $guides = [
            // ----------------------------------------------------
            // GETTING STARTED
            // ----------------------------------------------------
            [
                'title' => 'Admin Panel & Navigation Overview',
                'slug' => 'getting-started',
                'category' => 'getting_started',
                'subcategory' => null,
                'icon' => 'compass',
                'summary' => 'Learn how to navigate the admin panel, access key sections, and understand the basic system layout.',
                'visible_roles' => ['admin', 'store_admin', 'staff'],
                'sort_order' => 1,
                'content' => <<<HTML
<div class="space-y-6">
    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">What is this?</h3>
        <p class="text-surface-600 leading-relaxed">The Admin Panel is your central command center for managing customer orders, products, store locations, and staff settings. Everything you need to manage day-to-day operations can be accessed from the left sidebar menu.</p>
    </section>

    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">How to Navigate the Admin Panel</h3>
        <ol class="list-decimal list-inside space-y-2 text-surface-700 font-medium">
            <li><strong>Sidebar Menu:</strong> Click any section name in the left menu to open that area (e.g., Orders, Catalog, Stores).</li>
            <li><strong>Catalog Accordion:</strong> Click <em>Catalog</em> to expand sub-sections like Products, Categories, Card Types/Sizes, Templates, Coupons, Events, and Paper Types.</li>
            <li><strong>Top Bar / View Site:</strong> Use the top bar to check your store profile or click <em>View Site</em> at the bottom left to view the customer-facing store.</li>
            <li><strong>Searching & Actions:</strong> Most management pages feature a top search bar and an action button (e.g., <em>Add Product</em> or <em>Create Coupon</em>) in the top-right corner.</li>
        </ol>
    </section>

    <section class="bg-brand-50/60 border border-brand-200/80 rounded-2xl p-4">
        <h4 class="font-bold text-brand-900 mb-1">Who can use this?</h4>
        <p class="text-sm text-brand-800">All administrative users (Admin, Store Admin, Store Staff) have access to the panel, though specific sections are customized based on your assigned role.</p>
    </section>
</div>
HTML,
            ],
            [
                'title' => 'Understanding the Dashboard',
                'slug' => 'understanding-dashboard',
                'category' => 'getting_started',
                'subcategory' => null,
                'icon' => 'layout-dashboard',
                'summary' => 'Quick guide to reading dashboard stats, revenue metrics, recent orders, and daily activity.',
                'visible_roles' => ['admin', 'store_admin', 'staff'],
                'sort_order' => 2,
                'content' => <<<HTML
<div class="space-y-6">
    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">What is the Dashboard for?</h3>
        <p class="text-surface-600 leading-relaxed">The Dashboard provides a real-time overview of your store's performance. It displays total sales, pending order counts, recent customer activity, and quick summaries so you can stay informed without manual searching.</p>
    </section>

    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">Key Dashboard Metrics Explained</h3>
        <ul class="list-disc list-inside space-y-2 text-surface-700 font-medium">
            <li><strong>Total Orders:</strong> The number of orders placed within the current period.</li>
            <li><strong>Total Revenue:</strong> Total earnings calculated from completed and confirmed orders.</li>
            <li><strong>Pending Orders:</strong> Orders that require immediate store review or fulfillment.</li>
            <li><strong>Recent Orders Table:</strong> Direct links to open and process the latest customer purchases.</li>
        </ul>
    </section>
</div>
HTML,
            ],
            [
                'title' => 'Understanding Statuses and User Roles',
                'slug' => 'understanding-statuses-roles',
                'category' => 'getting_started',
                'subcategory' => null,
                'icon' => 'shield-check',
                'summary' => 'Overview of order workflow statuses and user permission levels across the system.',
                'visible_roles' => ['admin', 'store_admin', 'staff'],
                'sort_order' => 3,
                'content' => <<<HTML
<div class="space-y-6">
    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">Order Status Definitions</h3>
        <div class="grid sm:grid-cols-2 gap-3 text-sm">
            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl">
                <span class="font-bold text-amber-800">Pending:</span> <span class="text-amber-700">Order placed by customer, awaiting initial store review.</span>
            </div>
            <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl">
                <span class="font-bold text-blue-800">Confirmed:</span> <span class="text-blue-700">Order approved for production & printing.</span>
            </div>
            <div class="p-3 bg-purple-50 border border-purple-200 rounded-xl">
                <span class="font-bold text-purple-800">Processing:</span> <span class="text-purple-700">Design is currently printing or being prepared.</span>
            </div>
            <div class="p-3  bg-gray-50 border  border-gray-200 rounded-xl">
                <span class="font-bold  text-gray-800">Shipped / Delivered:</span> <span class=" text-gray-700">Order dispatched or handed to customer.</span>
            </div>
            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl">
                <span class="font-bold text-rose-800">Cancelled / Refunded:</span> <span class="text-rose-700">Order cancelled or payment refunded.</span>
            </div>
        </div>
    </section>

    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">User Roles & Permissions</h3>
        <ul class="space-y-2 text-surface-700">
            <li><strong>Admin:</strong> Full access to all stores, products, settings, user accounts, and documentation management.</li>
            <li><strong>Store Admin:</strong> Full control over their assigned store location, store products, coupons, and orders.</li>
            <li><strong>Store Staff:</strong> Focused access for daily order processing, printing, and customer support.</li>
        </ul>
    </section>
</div>
HTML,
            ],

            // ----------------------------------------------------
            // CUSTOMER EXPERIENCE & CUSTOM ORDERING
            // ----------------------------------------------------
            [
                'title' => 'How Customers Edit Photos & Order on Mobile Phones (Mobile Quick Flow)',
                'slug' => 'mobile-custom-editing-ordering-guide',
                'category' => 'customer_experience',
                'subcategory' => null,
                'icon' => 'smartphone',
                'summary' => 'Comprehensive step-by-step layman guide explaining how mobile smartphone customers scan QR codes, pick store, card, size, template, edit, and checkout.',
                'visible_roles' => ['admin', 'store_admin', 'staff'],
                'sort_order' => 4,
                'content' => <<<HTML
<div class="space-y-6">
    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">📱 Overview: How Customers Order on Mobile Phones (Quick Flow)</h3>
        <p class="text-surface-600 leading-relaxed">The Mobile Quick Flow is designed specifically for customers browsing on smartphones or tablets. It provides a simple, touch-friendly 6-step workflow: Scan QR Code/Select Store → Select Card → Select Size → Select Template → Custom Editing → Mobile Checkout.</p>
    </section>

    <section class="bg-pink-50/70 border border-pink-200/80 rounded-2xl p-5 space-y-4">
        <h4 class="font-bold text-pink-900 text-base">Step 1: Scan QR Code or Select Store</h4>
        <p class="text-xs text-pink-950 font-medium leading-relaxed">Customer scans a local store QR code displayed inside a physical branch or opens the website link on their smartphone. If not pre-selected via QR scan, the customer selects their nearest Qrinto print store branch for express local pickup or delivery.</p>

        <h4 class="font-bold text-pink-900 text-base pt-2">Step 2: Select Card Category & Product</h4>
        <p class="text-xs text-pink-950 font-medium leading-relaxed">Customer browses card categories (e.g., Birthday Cards, Wedding Invitations, Holiday Greetings, Anniversary Cards, or Custom Photo Prints) and taps to select their desired card product type.</p>

        <h4 class="font-bold text-pink-900 text-base pt-2">Step 3: Select Card Size & Print Dimensions</h4>
        <p class="text-xs text-pink-950 font-medium leading-relaxed">Customer chooses their preferred physical card size and print dimensions (e.g., 4x6 inch, 5x7 inch, Square 5x5 inch, or Folded Greeting Card size).</p>

        <h4 class="font-bold text-pink-900 text-base pt-2">Step 4: Select Design Template</h4>
        <p class="text-xs text-pink-950 font-medium leading-relaxed">Customer chooses a pre-designed graphic template layout or selects a blank custom template to start creating their design.</p>
    </section>

    <section class="bg-surface-50 border border-surface-200 p-5 rounded-2xl space-y-4">
        <h4 class="font-bold text-surface-900 text-base">Step 5: Custom Editing (Photos, Text & Pages)</h4>
        <div class="space-y-3 text-sm text-surface-700">
            <div class="p-3.5 bg-white border border-surface-200 rounded-xl shadow-2xs">
                <strong class="text-surface-900 block mb-1">📸 Uploading Photos from Phone Gallery or Camera</strong>
                <p class="text-xs text-surface-600">Tap the <strong>"+ Add Photo"</strong> or <strong>"Upload Image"</strong> button. The customer can select photos directly from their smartphone photo gallery (iPhone Photo Library / Android Gallery) or snap a live photo using their phone camera.</p>
            </div>

            <div class="p-3.5 bg-white border border-surface-200 rounded-xl shadow-2xs">
                <strong class="text-surface-900 block mb-1">✨ Automatic Photo Alignment & Mask Fitting</strong>
                <p class="text-xs text-surface-600">Once uploaded, the photo automatically snaps into the card's designated picture frame area (masked window cutout) without spilling over card borders.</p>
            </div>

            <div class="p-3.5 bg-white border border-surface-200 rounded-xl shadow-2xs">
                <strong class="text-surface-900 block mb-1">👆 Touch Adjustments: Pinch, Zoom, & Drag</strong>
                <ul class="list-disc list-inside text-xs text-surface-600 space-y-1 mt-1">
                    <li><strong>Reposition:</strong> Touch and drag with a finger inside the picture frame to align faces or center people.</li>
                    <li><strong>Zoom & Scale:</strong> Use pinch-to-zoom (two fingers) or tap the <strong>+ / - Zoom buttons</strong> to resize the photo.</li>
                    <li><strong>Rotate:</strong> Tap the <strong>Rotate 90° button</strong> if a smartphone photo appears sideways or upside down.</li>
                </ul>
            </div>

            <div class="p-3.5 bg-white border border-surface-200 rounded-xl shadow-2xs">
                <strong class="text-surface-900 block mb-1">✍️ Editing Custom Messages & Wording</strong>
                <p class="text-xs text-surface-600">Tap any text line on the card preview to open the mobile keyboard and type custom names, greetings, or signature lines. Tap font style options to switch between script handwriting, bold sans-serif, or serif fonts, and choose contrasting text colors.</p>
            </div>

            <div class="p-3.5 bg-white border border-surface-200 rounded-xl shadow-2xs">
                <strong class="text-surface-900 block mb-1">📖 Swiping Multi-Page Card Layouts</strong>
                <p class="text-xs text-surface-600">For multi-page items (like 2-page flat cards or 4-page folded greeting cards), customers swipe left/right or tap bottom page dots to customize Page 1 (Front Cover), Page 2 & 3 (Inside Spread), and Page 4 (Back Cover).</p>
            </div>
        </div>
    </section>

    <section class=" bg-gray-50 border  border-gray-200 p-5 rounded-2xl space-y-3">
        <h4 class="font-bold  text-gray-950 text-base">Step 6: Paper Choice, Review, & Mobile Checkout</h4>
        <ol class="list-decimal list-inside space-y-2 text-sm  text-gray-900 font-medium">
            <li><strong>Select Paper Stock:</strong> Choose paper finish (e.g., Ultra-Glossy Photo Paper, Silk Matte, Premium Linen Stock).</li>
            <li><strong>Choose Quantity:</strong> Select number of printed copies needed with live instant price calculation.</li>
            <li><strong>Preview Card:</strong> Tap <strong>Full Preview</strong> to double-check spelling, layout alignment, and picture placement.</li>
            <li><strong>Checkout:</strong> Tap <strong>Add to Cart</strong> or <strong>Checkout Now</strong>. Enter pickup contact details (Name, Phone, Email).</li>
            <li><strong>Payment Option:</strong> Select <strong>Pay Online (PayPal / Credit Card)</strong> or <strong>Pay Cash at Store Pickup</strong>.</li>
            <li><strong>Order Confirmation:</strong> An instant order summary page appears with an Order Number (e.g., ORD-2026-1045) and real-time status tracker.</li>
        </ol>
    </section>
</div>
HTML,
            ],
            [
                'title' => 'How Customers Edit Photos & Order on Computers (Desktop Studio)',
                'slug' => 'desktop-custom-editing-ordering-guide',
                'category' => 'customer_experience',
                'subcategory' => null,
                'icon' => 'monitor',
                'summary' => 'Detailed step-by-step layman guide showing how desktop computer users pick store, card, size, template, edit in studio, and order.',
                'visible_roles' => ['admin', 'store_admin', 'staff'],
                'sort_order' => 5,
                'content' => <<<HTML
<div class="space-y-6">
    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">🖥️ Overview: How Customers Order on Computers & Laptops (Quick Flow PC)</h3>
        <p class="text-surface-600 leading-relaxed">The Desktop Quick Flow PC studio is optimized for computer users. It features an intuitive 6-step process: Select Store → Select Card → Select Size → Select Template → Custom Studio Editing → Checkout.</p>
    </section>

    <section class="bg-indigo-50/70 border border-indigo-200/80 rounded-2xl p-5 space-y-4">
        <h4 class="font-bold text-indigo-950 text-base">Step 1: Select Store Location</h4>
        <p class="text-xs text-indigo-900 font-medium leading-relaxed">Customer visits the Qrinto website on a laptop or desktop computer and selects their local Qrinto store branch for express same-day pickup or home delivery.</p>

        <h4 class="font-bold text-indigo-950 text-base pt-2">Step 2: Select Card Category & Product</h4>
        <p class="text-xs text-indigo-900 font-medium leading-relaxed">Customer explores high-definition card categories (e.g., Wedding Cards, Birthday Invitations, Business Prints, Holiday Cards) and clicks to select a product.</p>

        <h4 class="font-bold text-indigo-950 text-base pt-2">Step 3: Select Card Size & Orientation</h4>
        <p class="text-xs text-indigo-900 font-medium leading-relaxed">Customer selects their preferred physical card size (e.g., 4x6", 5x7", 6x8", or Square) and paper orientation (Portrait or Landscape).</p>

        <h4 class="font-bold text-indigo-950 text-base pt-2">Step 4: Select Design Template</h4>
        <p class="text-xs text-indigo-900 font-medium leading-relaxed">Customer selects a curated graphic design template or starts with a blank custom canvas layout.</p>
    </section>

    <section class="bg-surface-50 border border-surface-200 p-5 rounded-2xl space-y-4">
        <h4 class="font-bold text-surface-900 text-base">Step 5: Custom Studio Editing (Photos, Text & 3D Preview)</h4>
        <div class="space-y-3 text-sm text-surface-700">
            <div class="p-3.5 bg-white border border-surface-200 rounded-xl shadow-2xs">
                <strong class="text-surface-900 block mb-1">📁 Drag & Drop Photo Uploads</strong>
                <p class="text-xs text-surface-600">Customers can drag image files directly from desktop folders on Windows PC or Mac into the canvas uploader, or click <strong>Browse Files</strong> to select multiple high-resolution photos at once.</p>
            </div>

            <div class="p-3.5 bg-white border border-surface-200 rounded-xl shadow-2xs">
                <strong class="text-surface-900 block mb-1">🖼️ Photo Library & Masked Placement</strong>
                <p class="text-xs text-surface-600">Uploaded photos appear in the left-hand Photo Gallery sidebar. Customers click or drag any photo onto target frame cutouts (masked openings) on the card layout. Photos automatically fit and crop inside frame boundaries.</p>
            </div>

            <div class="p-3.5 bg-white border border-surface-200 rounded-xl shadow-2xs">
                <strong class="text-surface-900 block mb-1">🖱️ Mouse Controls: Drag, Wheel Zoom, & Rotate</strong>
                <ul class="list-disc list-inside text-xs text-surface-600 space-y-1 mt-1">
                    <li><strong>Position:</strong> Click and hold the mouse button to slide the photo inside the frame.</li>
                    <li><strong>Zoom:</strong> Scroll the mouse wheel or drag the scale slider bar to adjust photo size.</li>
                    <li><strong>Orientation:</strong> Use horizontal/vertical flip buttons or rotate handles for precise adjustments.</li>
                </ul>
            </div>

            <div class="p-3.5 bg-white border border-surface-200 rounded-xl shadow-2xs">
                <strong class="text-surface-900 block mb-1">✍️ Editing Text & Typography</strong>
                <p class="text-xs text-surface-600">Click any text line on the canvas to edit wording live. Select from font families (Script, Serif, Sans-Serif), adjust font size, alignment, and pick colors using the top toolbar color picker.</p>
            </div>

            <div class="p-3.5 bg-white border border-surface-200 rounded-xl shadow-2xs">
                <strong class="text-surface-900 block mb-1">🃏 Interactive 3D Deck Fan & Page Tabs</strong>
                <p class="text-xs text-surface-600">Switch between card pages (Front Cover, Inside Spread, Back Cover) using top page tabs or the 3D Card Fan deck viewer to preview the complete card spread before ordering.</p>
            </div>
        </div>
    </section>

    <section class=" bg-gray-50 border  border-gray-200 p-5 rounded-2xl space-y-3">
        <h4 class="font-bold  text-gray-950 text-base">Step 6: Paper Choice, Review & Desktop Checkout</h4>
        <ol class="list-decimal list-inside space-y-2 text-sm  text-gray-900 font-medium">
            <li><strong>Paper Finish & Stock:</strong> Choose paper weight and finish (e.g. Matte Cardstock, Premium Glossy, Linen Finish).</li>
            <li><strong>Quantity & Pricing:</strong> Select quantity (e.g., 25, 50, 100 cards) with live instant price calculation.</li>
            <li><strong>Checkout:</strong> Click <strong>Proceed to Checkout</strong>. Enter customer name, email, and mobile phone.</li>
            <li><strong>Payment Method:</strong> Select <strong>Pay Online (PayPal / Credit Card)</strong> or <strong>Pay Cash at Store Pickup</strong>.</li>
            <li><strong>Instant Receipt & Tracking:</strong> Upon placing the order, a digital receipt with Order # is generated along with a direct tracking link to check printing progress.</li>
        </ol>
    </section>
</div>
HTML,
            ],

            // ----------------------------------------------------
            // ORDERS
            // ----------------------------------------------------
            [
                'title' => 'Orders Overview & Workflow',
                'slug' => 'orders',
                'category' => 'orders',
                'subcategory' => null,
                'icon' => 'package',
                'summary' => 'Learn how customer orders are received, organized, and managed from placement to fulfillment.',
                'visible_roles' => ['admin', 'store_admin', 'staff'],
                'sort_order' => 10,
                'content' => <<<HTML
<div class="space-y-6">
    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">What is the Orders section?</h3>
        <p class="text-surface-600 leading-relaxed">The Orders section lists every purchase placed by customers across your stores. Here you can inspect order details, view all customized page images, download print-ready PDFs, update fulfillment status, and send print jobs to production.</p>
    </section>

    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">How to Find and Search Orders</h3>
        <ol class="list-decimal list-inside space-y-2 text-surface-700 font-medium">
            <li>Click <strong>Orders</strong> in the left sidebar menu.</li>
            <li>Use the top search bar to enter the <strong>Order Number</strong> (e.g., ORD-2026-1001) or <strong>Customer Name</strong>.</li>
            <li>Use status filter dropdowns to narrow results by <em>Pending</em>, <em>Paid</em>, or <em>Processing</em>.</li>
            <li>Click any order row to open its complete detail page.</li>
        </ol>
    </section>
</div>
HTML,
            ],
            [
                'title' => 'How to Open and Process an Order',
                'slug' => 'how-to-process-an-order',
                'category' => 'orders',
                'subcategory' => null,
                'icon' => 'file-check',
                'summary' => 'Step-by-step guide to updating order status, checking customer shipping info, and marking orders complete.',
                'visible_roles' => ['admin', 'store_admin', 'staff'],
                'sort_order' => 11,
                'content' => <<<HTML
<div class="space-y-6">
    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">Step-by-Step Order Processing</h3>
        <ol class="list-decimal list-inside space-y-3 text-surface-700 font-medium">
            <li><strong>Open Orders List:</strong> Navigate to <em>Orders</em> from the left menu.</li>
            <li><strong>Select Order:</strong> Click on the order you wish to process.</li>
            <li><strong>Review Order Items:</strong> Inspect the ordered products, selected paper options, dimensions, and customer custom text.</li>
            <li><strong>Preview & Print Design:</strong> View the customer's edited canvas page thumbnails. Click <strong>Download PDF</strong> or <strong>Print Design</strong> next to each item.</li>
            <li><strong>Update Order Status:</strong> Select the new status (e.g., <em>Processing</em> or <em>Shipped</em>) from the status dropdown card on the right side and click <strong>Update Status</strong>.</li>
            <li><strong>Add Tracking & Notes:</strong> Enter optional courier tracking numbers or internal staff notes before saving.</li>
        </ol>
    </section>

    <section class="bg-surface-100 p-4 rounded-2xl border border-surface-200">
        <h4 class="font-bold text-surface-900 mb-1">What happens after saving?</h4>
        <p class="text-sm text-surface-600">The order status will be updated immediately. Automated confirmation email updates with tracking details are sent to the customer.</p>
    </section>
</div>
HTML,
            ],
            [
                'title' => 'Understanding Multiple Designs & Items in an Order',
                'slug' => 'order-items-designs',
                'category' => 'orders',
                'subcategory' => null,
                'icon' => 'layers',
                'summary' => 'Learn how orders containing multiple customized items and multi-page designs are structured.',
                'visible_roles' => ['admin', 'store_admin', 'staff'],
                'sort_order' => 12,
                'content' => <<<HTML
<div class="space-y-6">
    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">How Multi-Item Orders Work</h3>
        <p class="text-surface-600 leading-relaxed">Customers can customize multiple cards or print products in a single checkout. On the order detail page, each item is listed separately with its own customized canvas image previews and dedicated action buttons.</p>
    </section>

    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">Dedicated Item Action Buttons</h3>
        <ul class="space-y-2 text-surface-700 font-medium">
            <li>📄 <strong>Download PDF:</strong> Downloads a print-ready vector PDF for that specific item.</li>
            <li>🖼️ <strong>Download Image:</strong> Downloads a high-resolution JPG image preview of the customer's design.</li>
            <li>🖨️ <strong>Print Design:</strong> Triggers immediate direct browser printing for that specific item.</li>
        </ul>
    </section>
</div>
HTML,
            ],

            // ----------------------------------------------------
            // CATALOG - PRODUCTS
            // ----------------------------------------------------
            [
                'title' => 'Managing Products, Image Marking & Product Masking',
                'slug' => 'products',
                'category' => 'catalog',
                'subcategory' => 'products',
                'icon' => 'box',
                'summary' => 'Complete guide to adding products, setting prices, uploading images/overlays, configuring page counts, and using the Product Mask Editor.',
                'visible_roles' => ['admin', 'store_admin', 'staff'],
                'sort_order' => 20,
                'content' => <<<HTML
<div class="space-y-6">
    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">What are Products?</h3>
        <p class="text-surface-600 leading-relaxed">Products are the customizable cards, flyers, and print items available for purchase in your store. You can manage product pricing, upload design template images, set print dimensions, configure photo cutouts (masking), and control product visibility.</p>
    </section>

    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">How to Add a New Product</h3>
        <ol class="list-decimal list-inside space-y-2.5 text-surface-700 font-medium">
            <li>Open <strong>Catalog → Products</strong> from the left sidebar.</li>
            <li>Click the green <strong>Add Product</strong> button in the top right.</li>
            <li>Enter the <strong>Product Name</strong>, select a <strong>Category</strong>, and set the <strong>Base Price</strong>.</li>
            <li>Select the <strong>Number of Pages</strong> (1 Page for single print, 2 Pages for double-sided, or 4 Pages for folded cards).</li>
            <li>Set the <strong>PDF Export Orientation</strong> (Portrait or Landscape).</li>
            <li>Upload Product Images (Featured Image, Sample Image, and Overlay Image).</li>
            <li>Click <strong>Save Product</strong>.</li>
        </ol>
    </section>

    <section class="bg-surface-50 border border-surface-200 p-5 rounded-2xl space-y-3">
        <h3 class="text-lg font-bold text-surface-900 flex items-center gap-2">
            🖼️ Understanding Image Marking Types
        </h3>
        <ul class="space-y-2 text-xs text-surface-700 leading-relaxed">
            <li><strong>Featured Image:</strong> The primary thumbnail photo displayed in the storefront catalog, category grid, and shop listing.</li>
            <li><strong>Sample Image:</strong> The default design template background rendered inside the customer online editor canvas.</li>
            <li><strong>Overlay Image:</strong> A transparent foreground frame/border layer uploaded by the admin that renders ON TOP of the customer's uploaded photos (e.g. decorative foil borders, text frames, or window cutouts).</li>
            <li><strong>Extra Images:</strong> Additional sample display photos shown on the customer product detail page gallery.</li>
        </ul>
    </section>

    <section class="bg-brand-50/60 border border-brand-200 p-5 rounded-2xl space-y-3">
        <h3 class="text-lg font-bold text-brand-900 flex items-center gap-2">
            ✂️ Product Masking & The Mask Editor
        </h3>
        <p class="text-xs text-brand-800 leading-relaxed"><strong>What is Product Masking?</strong> Product masking allows administrators to draw precise photo placement frames or cutouts on a product template. This ensures that when a customer uploads their personal photo, the photo is automatically clipped, cropped, and aligned perfectly inside the defined picture frame without spilling over card borders.</p>

        <h4 class="font-bold text-xs text-brand-900 pt-2">How to Use the Mask Editor:</h4>
        <ol class="list-decimal list-inside space-y-2 text-xs text-brand-800 font-medium">
            <li>Go to <strong>Catalog → Products</strong>.</li>
            <li>Find your product and click the <strong>Mask Editor</strong> (or Mask button).</li>
            <li>The interactive canvas opens showing your product's template image.</li>
            <li>Click <strong>Add Mask Region</strong> and choose a shape (Rectangle or Custom Polygon).</li>
            <li>Drag and resize the handles over the target photo frame opening on the card design.</li>
            <li>Click <strong>Save Mask</strong>.</li>
        </ol>
        <p class="text-xs font-semibold text-brand-900 pt-1">What happens after saving? Customer uploaded photos will automatically scale, snap, and clip into this exact masked region inside the online card editor!</p>
    </section>

    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">Page Configurations Explained</h3>
        <div class="grid sm:grid-cols-3 gap-3 text-xs">
            <div class="p-3 bg-surface-50 border border-surface-200 rounded-xl">
                <span class="font-bold text-surface-900 block mb-1">1 Page (Single-Sided)</span>
                <span class="text-surface-600">Standard single-sided photo prints, flat invitation cards, or posters.</span>
            </div>
            <div class="p-3 bg-surface-50 border border-surface-200 rounded-xl">
                <span class="font-bold text-surface-900 block mb-1">2 Pages (Double-Sided)</span>
                <span class="text-surface-600">Double-sided flat cards (Page 1 Front Cover + Page 2 Back Message).</span>
            </div>
            <div class="p-3 bg-surface-50 border border-surface-200 rounded-xl">
                <span class="font-bold text-surface-900 block mb-1">4 Pages (Folded Card)</span>
                <span class="text-surface-600">Folded greeting cards (Page 1 Front, Page 2 Inside Left, Page 3 Inside Right, Page 4 Back).</span>
            </div>
        </div>
    </section>

    <section class="bg-amber-50 border border-amber-200 p-4 rounded-2xl">
        <h4 class="font-bold text-amber-900 mb-1">Common Product Troubleshooting</h4>
        <ul class="list-disc list-inside space-y-1 text-xs text-amber-800">
            <li>If a product is not visible in your store, make sure <strong>Is Active</strong> is checked.</li>
            <li>If customer uploaded photos appear behind frames incorrectly, verify that your <strong>Overlay Image</strong> has a transparent window cutout.</li>
            <li>If photos are misaligned, re-open the <strong>Mask Editor</strong> to adjust mask polygon handles.</li>
        </ul>
    </section>
</div>
HTML,
            ],

            // ----------------------------------------------------
            // CATALOG - CATEGORIES
            // ----------------------------------------------------
            [
                'title' => 'Managing Categories',
                'slug' => 'categories',
                'category' => 'catalog',
                'subcategory' => 'categories',
                'icon' => 'grid-2x2',
                'summary' => 'Organize products into intuitive categories like Wedding Cards, Birthday Cards, and Business Prints.',
                'visible_roles' => ['admin', 'store_admin', 'staff'],
                'sort_order' => 21,
                'content' => <<<HTML
<div class="space-y-6">
    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">What are Categories used for?</h3>
        <p class="text-surface-600 leading-relaxed">Categories group related products together so customers can easily browse items by occasion or type (e.g., Anniversary, Invitation, Business Cards).</p>
    </section>

    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">How to Add a Category</h3>
        <ol class="list-decimal list-inside space-y-2 text-surface-700 font-medium">
            <li>Open <strong>Catalog → Categories</strong>.</li>
            <li>Click <strong>Add Category</strong>.</li>
            <li>Enter the Category Name and upload an icon or banner image if required.</li>
            <li>Click <strong>Save Category</strong>.</li>
        </ol>
    </section>
</div>
HTML,
            ],

            // ----------------------------------------------------
            // CATALOG - CARD TYPES / SIZES
            // ----------------------------------------------------
            [
                'title' => 'Managing Card Types & Print Sizes',
                'slug' => 'card-types-sizes',
                'category' => 'catalog',
                'subcategory' => 'card_types',
                'icon' => 'layers',
                'summary' => 'Define standard card sizes, custom print dimensions (e.g., 5x7 inches, 4x6 inches), and paper orientations.',
                'visible_roles' => ['admin'],
                'sort_order' => 22,
                'content' => <<<HTML
<div class="space-y-6">
    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">What are Card Types & Sizes?</h3>
        <p class="text-surface-600 leading-relaxed">Card Types and Sizes set the precise physical dimensions (width, height, unit) and PDF export orientation for print products.</p>
    </section>

    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">How to Add a Size Option</h3>
        <ol class="list-decimal list-inside space-y-2 text-surface-700 font-medium">
            <li>Navigate to <strong>Catalog → Card Types/Sizes</strong>.</li>
            <li>Click <strong>Add New Size</strong>.</li>
            <li>Enter the Title (e.g., "5x7 Inch Standard"), Width (5), Height (7), and Unit (inch).</li>
            <li>Click <strong>Save Size</strong>.</li>
        </ol>
    </section>
</div>
HTML,
            ],

            // ----------------------------------------------------
            // CATALOG - TEMPLATES
            // ----------------------------------------------------
            [
                'title' => 'Managing Design Templates',
                'slug' => 'templates',
                'category' => 'catalog',
                'subcategory' => 'templates',
                'icon' => 'layout-template',
                'summary' => 'Create and manage customizable graphic design templates for the customer online customizer.',
                'visible_roles' => ['admin'],
                'sort_order' => 23,
                'content' => <<<HTML
<div class="space-y-6">
    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">What are Templates?</h3>
        <p class="text-surface-600 leading-relaxed">Templates provide pre-designed card layouts that customers can edit directly in the browser customizer by adding custom photos and text.</p>
    </section>

    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">How to Manage Templates</h3>
        <ol class="list-decimal list-inside space-y-2 text-surface-700 font-medium">
            <li>Go to <strong>Catalog → Templates</strong>.</li>
            <li>Click <strong>Add Template</strong> or edit an existing template.</li>
            <li>Upload template graphics and assign it to a category.</li>
            <li>Toggle <em>Active Status</em> to make the template available online.</li>
        </ol>
    </section>
</div>
HTML,
            ],

            // ----------------------------------------------------
            // CATALOG - COUPONS
            // ----------------------------------------------------
            [
                'title' => 'Creating & Managing Discount Coupons',
                'slug' => 'coupons',
                'category' => 'catalog',
                'subcategory' => 'coupons',
                'icon' => 'tag',
                'summary' => 'Set up promotional discount codes, percentage or fixed savings, minimum order limits, and expiration dates.',
                'visible_roles' => ['admin', 'store_admin'],
                'sort_order' => 24,
                'content' => <<<HTML
<div class="space-y-6">
    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">What are Coupons?</h3>
        <p class="text-surface-600 leading-relaxed">Coupons allow customers to receive discounts at checkout by entering a promotional promo code (e.g., SAVE10, HOLIDAY20).</p>
    </section>

    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">How to Create a Coupon Code</h3>
        <ol class="list-decimal list-inside space-y-2 text-surface-700 font-medium">
            <li>Open <strong>Catalog → Coupons</strong>.</li>
            <li>Click <strong>Create Coupon</strong>.</li>
            <li>Enter the <strong>Coupon Code</strong> (e.g., WELCOME15).</li>
            <li>Select Discount Type (Percentage % or Fixed Amount $).</li>
            <li>Enter the Discount Value and set optional start and expiry dates.</li>
            <li>Click <strong>Save Coupon</strong>.</li>
        </ol>
    </section>
</div>
HTML,
            ],

            // ----------------------------------------------------
            // CATALOG - EVENTS
            // ----------------------------------------------------
            [
                'title' => 'Managing Events & Occasion Collections',
                'slug' => 'events',
                'category' => 'catalog',
                'subcategory' => 'events',
                'icon' => 'calendar',
                'summary' => 'Highlight seasonal occasions like Christmas, Graduation, or Valentine\'s Day with curated event collections.',
                'visible_roles' => ['admin', 'store_admin'],
                'sort_order' => 25,
                'content' => <<<HTML
<div class="space-y-6">
    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">What are Events?</h3>
        <p class="text-surface-600 leading-relaxed">Events allow you to feature special seasonal themes on the storefront homepage and filter products by holiday or celebration.</p>
    </section>

    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">How to Add an Event</h3>
        <ol class="list-decimal list-inside space-y-2 text-surface-700 font-medium">
            <li>Go to <strong>Catalog → Events</strong>.</li>
            <li>Click <strong>Add Event</strong>.</li>
            <li>Enter Event Name, upload a banner, and set start/end display dates.</li>
            <li>Click <strong>Save Event</strong>.</li>
        </ol>
    </section>
</div>
HTML,
            ],

            // ----------------------------------------------------
            // CATALOG - PAPER TYPES
            // ----------------------------------------------------
            [
                'title' => 'Managing Paper Types & Stock Options',
                'slug' => 'paper-types',
                'category' => 'catalog',
                'subcategory' => 'paper_types',
                'icon' => 'scroll-text',
                'summary' => 'Configure paper finish choices (Matte, Glossy, Linen, Heavyweight) and option pricing for print jobs.',
                'visible_roles' => ['admin', 'store_admin'],
                'sort_order' => 26,
                'content' => <<<HTML
<div class="space-y-6">
    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">What are Paper Types?</h3>
        <p class="text-surface-600 leading-relaxed">Paper Types define the paper weight and finish choices presented to customers when ordering prints (e.g., Premium Matte 300gsm, High Gloss Photo Paper).</p>
    </section>

    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">How to Add a Paper Type</h3>
        <ol class="list-decimal list-inside space-y-2 text-surface-700 font-medium">
            <li>Navigate to <strong>Catalog → Paper Types</strong>.</li>
            <li>Click <strong>Add Paper Type</strong>.</li>
            <li>Enter Title, Description, and any additional cost adjustment.</li>
            <li>Click <strong>Save Paper Type</strong>.</li>
        </ol>
    </section>
</div>
HTML,
            ],

            // ----------------------------------------------------
            // STORES
            // ----------------------------------------------------
            [
                'title' => 'Managing Stores, Network Printers & FTP Hot-Folders',
                'slug' => 'stores',
                'category' => 'stores',
                'subcategory' => null,
                'icon' => 'store',
                'summary' => 'Manage physical store locations, network printer IP configurations, FTP hot-folders, and staff store assignments.',
                'visible_roles' => ['admin', 'store_admin'],
                'sort_order' => 30,
                'content' => <<<HTML
<div class="space-y-6">
    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">What is the Stores section?</h3>
        <p class="text-surface-600 leading-relaxed">Stores enables multi-location administration. You can configure physical store information, generate store QR scanning codes, connect direct network printers, and set up automated FTP hot-folders for Noritsu print equipment.</p>
    </section>

    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">How to Update Store & Printer Settings</h3>
        <ol class="list-decimal list-inside space-y-2.5 text-surface-700 font-medium">
            <li>Click <strong>Stores</strong> in the left sidebar.</li>
            <li>Click <strong>Edit</strong> on your store location.</li>
            <li><strong>Store Details:</strong> Update Store Name, Store Code, Phone, Email, and Address.</li>
            <li><strong>Direct Printer Settings:</strong> Enter your local network Printer IP address and printer port for automated browser dispatch.</li>
            <li><strong>FTP Hot-Folder Setup:</strong> Enter FTP Host, Port, Username, Password, and target Hot-Folder Path for Noritsu production machines.</li>
            <li>Click <strong>Test FTP Connection</strong> to verify machine connectivity.</li>
            <li>Click <strong>Save Store Details</strong>.</li>
        </ol>
    </section>
</div>
HTML,
            ],

            // ----------------------------------------------------
            // USERS
            // ----------------------------------------------------
            [
                'title' => 'Managing Users & User Roles',
                'slug' => 'users',
                'category' => 'users',
                'subcategory' => null,
                'icon' => 'users',
                'summary' => 'Guide for Super Admins to create user accounts, assign roles (Admin, Store Admin, Staff), and deactivate access.',
                'visible_roles' => ['admin'],
                'sort_order' => 40,
                'content' => <<<HTML
<div class="space-y-6">
    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">What is Users Management?</h3>
        <p class="text-surface-600 leading-relaxed">The Users section manages all system accounts. Administrators can view registered accounts, create staff logins, assign store permissions, and activate or deactivate accounts as needed.</p>
    </section>

    <section>
        <h3 class="text-xl font-bold text-surface-900 mb-2">How to Add or Edit a User Account</h3>
        <ol class="list-decimal list-inside space-y-2 text-surface-700 font-medium">
            <li>Click <strong>Users</strong> in the left menu.</li>
            <li>Click <strong>Add User</strong> or select <strong>Edit</strong> on an existing user.</li>
            <li>Enter Name, Email, Password, and select the assigned <strong>Role</strong> (Admin, Store Admin, Staff).</li>
            <li>Select assigned Store Location if creating a Store Admin or Staff user.</li>
            <li>Click <strong>Save User</strong>.</li>
        </ol>
    </section>
</div>
HTML,
            ],
        ];

        foreach ($guides as $data) {
            DocumentationGuide::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
