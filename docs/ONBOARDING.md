# Qrinto Print Studio - Developer Onboarding Guide

**Version:** 1.0  
**Last Updated:** August 3, 2026  
**Audience:** New developers joining the Qrinto project

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Important URLs](#2-important-urls)
3. [Login & Access Information](#3-login--access-information)
4. [User Roles](#4-user-roles)
5. [Admin Portal Documentation](#5-admin-portal-documentation)
6. [Store Portal Documentation](#6-store-portal-documentation)
7. [Permission Matrix](#7-permission-matrix)
8. [Customer Website](#8-customer-website)
9. [Customer Resource Center](#9-customer-resource-center)
10. [Database Overview](#10-database-overview)
11. [API Documentation](#11-api-documentation)
12. [Development Environment](#12-development-environment)
13. [Application Workflow](#13-application-workflow)
14. [Folder Structure](#14-folder-structure)
15. [Troubleshooting](#15-troubleshooting)
16. [Best Practices](#16-best-practices)

---

## 1. Project Overview

### What is Qrinto?

Qrinto Print Studio is a **custom photo printing and personalized product e-commerce platform**. It enables customers to design and order printed products (greeting cards, magnets, custom prints) through both in-store kiosks and a web-based interface. Each store has a unique QR code that customers scan to begin the ordering process.

### Business Purpose

- Allow customers to create personalized print products (greeting cards, magnets, photo prints)
- Support multi-store operations with individual store management
- Enable in-store kiosk ordering via QR code scanning
- Provide both online payment (PayPal, Stripe, Razorpay) and cash-at-counter options
- Integrate directly with Noritsu 931-BL photo printers via a Windows print agent
- Support USD and CAD currencies with automatic conversion based on store location

### Technology Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| **Backend Framework** | Laravel | 12.x |
| **Language** | PHP | 8.2+ |
| **Database** | MySQL / SQLite | - |
| **Frontend (Blade views)** | Alpine.js | 3.x |
| **Frontend (Noritsu editor)** | React | 19.x |
| **CSS Framework** | Tailwind CSS | 3.x |
| **Build Tool** | Vite | 7.x |
| **Canvas/Image Editor** | Fabric.js | 7.x |
| **PDF Generation** | DomPDF (barryvdh/laravel-dompdf) | 3.x |
| **Image Processing** | Intervention Image | 3.x |
| **Authentication** | Laravel Breeze + Socialite | 2.x |
| **Payment Gateways** | PayPal REST API v2, Stripe, Razorpay | - |
| **AI Integration** | OpenRouter API | - |
| **Print Agent** | .NET 8 (C# Windows Service) | net8.0 |
| **Icons** | Lucide Icons | 1.x |
| **Onboarding Tours** | Driver.js | - |
| **PWA** | Service Worker + manifest.json | - |

### Architecture Overview

```
┌─────────────────────────────────────────────────────────┐
│                    CLIENT LAYER                          │
│  ┌──────────┐  ┌──────────┐  ┌───────────┐             │
│  │  Mobile   │  │ Desktop  │  │  Noritsu  │             │
│  │Quick Flow │  │ PC Flow  │  │React Editor│            │
│  │(Alpine.js)│  │(Alpine.js)│ │(React 19) │            │
│  └─────┬─────┘  └─────┬─────┘ └─────┬─────┘            │
│        └───────────────┼─────────────┘                  │
└────────────────────────┼────────────────────────────────┘
                         │ HTTP / AJAX
┌────────────────────────┼────────────────────────────────┐
│                 APPLICATION LAYER                        │
│  ┌─────────────────────┴──────────────────────┐         │
│  │           Laravel 12.x (PHP 8.2+)          │         │
│  │  ┌──────────┐ ┌──────────┐ ┌────────────┐  │        │
│  │  │Controllers│ │ Services │ │   Models   │  │        │
│  │  │ (45 files)│ │  (4)     │ │  (25)      │  │        │
│  │  └──────────┘ └──────────┘ └────────────┘  │        │
│  │  ┌──────────┐ ┌──────────┐ ┌────────────┐  │        │
│  │  │   Mail   │ │   Jobs   │ │ Middleware  │  │        │
│  │  │  (6)     │ │   (1)    │ │   (1)      │  │        │
│  │  └──────────┘ └──────────┘ └────────────┘  │        │
│  └────────────────────────────────────────────┘         │
└────────────────────────┼────────────────────────────────┘
                         │
┌────────────────────────┼────────────────────────────────┐
│                   DATA LAYER                             │
│  ┌──────────┐  ┌───────────┐  ┌──────────────┐         │
│  │  MySQL   │  │  Storage  │  │  Session/    │          │
│  │ Database │  │ (uploads, │  │  Cache       │          │
│  │ (30+ tbl)│  │  PDFs)    │  │ (database)   │         │
│  └──────────┘  └───────────┘  └──────────────┘         │
└─────────────────────────────────────────────────────────┘
                         │
┌────────────────────────┼────────────────────────────────┐
│               EXTERNAL SERVICES                          │
│  ┌────────┐ ┌────────┐ ┌──────────┐ ┌───────────────┐  │
│  │ PayPal │ │ Stripe │ │ Razorpay │ │  OpenRouter   │  │
│  │  API   │ │  API   │ │   API    │ │  (AI/LLM)     │  │
│  └────────┘ └────────┘ └──────────┘ └───────────────┘  │
│  ┌────────┐ ┌────────┐ ┌──────────────────────────┐    │
│  │ Google │ │Facebook│ │ OpenStreetMap Nominatim   │    │
│  │ OAuth  │ │ OAuth  │ │ (Geocoding)              │    │
│  └────────┘ └────────┘ └──────────────────────────┘    │
└─────────────────────────────────────────────────────────┘
                         │
┌────────────────────────┼────────────────────────────────┐
│              PRINT INFRASTRUCTURE                        │
│  ┌──────────────────────────────────────────────┐       │
│  │     QrintoPrintAgent (.NET 8 C# Service)     │       │
│  │  ┌────────────┐  ┌──────────┐  ┌──────────┐  │      │
│  │  │ Job Receiver│  │  Print   │  │  Status  │  │      │
│  │  │ (WebSocket) │  │ Executor │  │ Reporter │  │      │
│  │  └────────────┘  └──────────┘  └──────────┘  │      │
│  │              ↓                                │       │
│  │     Noritsu 931-BL Printer                    │       │
│  └──────────────────────────────────────────────┘       │
└─────────────────────────────────────────────────────────┘
```

### High-Level Workflow

1. **Customer scans QR code** at a store → store is set in session
2. **Selects product type** (e.g., Greeting Cards) → selects size variant (e.g., 5×7)
3. **Browses design templates** filtered by category
4. **Customizes design** - uploads photos, positions within mask zones
5. **Adds to cart** or proceeds to single-item checkout
6. **Pays** via PayPal/Stripe/Razorpay or chooses cash at counter
7. **Order created** → emails sent to customer, admin, and store
8. **Store processes order** → prints via QrintoPrintAgent → customer picks up

---

## 2. Important URLs

### Application URLs

| URL | Description |
|-----|-------------|
| **Customer Website (Mobile)** | `{APP_URL}/` - Quick Flow mobile-optimized customer experience |
| **Customer Website (Desktop)** | `{APP_URL}/pc/` - Desktop-optimized customer experience |
| **Store Finder** | `{APP_URL}/find-store` - Find nearby stores by location or search |
| **Custom Print** | `{APP_URL}/custom-print` - Direct file upload printing (skip templates) |
| **Order Tracking** | `{APP_URL}/track` - Customer order tracking by order number |
| **Customer Account** | `{APP_URL}/my-account` - Customer dashboard, order history |
| **Admin Panel** | `{APP_URL}/admin` - Full admin dashboard |
| **Login** | `{APP_URL}/login` - Authentication page |
| **Store QR Scan** | `{APP_URL}/store/{storeCode}` - QR code entry point per store |
| **Store QR Display** | `{APP_URL}/store/{storeCode}/qr` - Printable QR code page |
| **Noritsu Editor** | `{APP_URL}/noritsu/` - React-based photo editor |
| **Cart** | `{APP_URL}/cart` - Shopping cart |

### API URLs

| URL | Description |
|-----|-------------|
| **Product Price Calculation** | `POST {APP_URL}/api/v1/products/{product}/calculate-price` |
| **Image Upload** | `POST {APP_URL}/api/v1/upload` |
| **Cart API** | `{APP_URL}/api/v1/cart/*` - Add, update, remove, coupon operations |
| **Noritsu API** | `{APP_URL}/api/noritsu/v1/*` - Stores, templates, orders for Noritsu app |
| **Print Agent Polling** | `GET {APP_URL}/api/print-jobs/pending` - Agent polls for print jobs |
| **Print Agent Messages** | `POST {APP_URL}/api/agent/messages` - Agent status updates |
| **User Design Save** | `POST {APP_URL}/api/user-designs/save` |
| **AI Chat** | `POST {APP_URL}/chat` - OpenRouter AI integration |

### External Service URLs

| Service | URL / Endpoint |
|---------|----------------|
| **PayPal Sandbox** | `https://api-m.sandbox.paypal.com/` |
| **PayPal Live** | `https://api-m.paypal.com/` |
| **OpenRouter AI** | `https://openrouter.ai/api/v1/chat/completions` |
| **OpenStreetMap Geocoding** | `https://nominatim.openstreetmap.org/search` |
| **Google OAuth** | Configured via `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` |
| **Facebook OAuth** | Configured via `FACEBOOK_CLIENT_ID` / `FACEBOOK_CLIENT_SECRET` |

### Infrastructure URLs

| Resource | Details |
|----------|---------|
| **Git Repository** | **Information Required** - Repository URL not found in codebase |
| **Staging Environment** | **Information Required** - Not configured in codebase |
| **Production Environment** | **Information Required** - Check `.env` for `APP_URL` |
| **CDN** | **Information Required** - No CDN configuration found; assets served locally |
| **Storage URL** | `{APP_URL}/storage/` - Symlinked to `storage/app/public/` |
| **API Documentation** | **Information Required** - No Swagger/OpenAPI spec found |

---

## 3. Login & Access Information

### Login Points

| Portal | URL | Method |
|--------|-----|--------|
| **Admin / Store Admin Login** | `{APP_URL}/login` | Email + Password |
| **Customer Login** | `{APP_URL}/login` | Email + Password, or Google/Facebook OAuth |
| **Social Login (Google)** | `{APP_URL}/auth/google/redirect` | OAuth 2.0 redirect |
| **Social Login (Facebook)** | `{APP_URL}/auth/facebook/redirect` | OAuth 2.0 redirect |

### Authentication Flow

1. User visits `/login` → `AuthenticatedSessionController@create` renders login form
2. User submits credentials → `LoginRequest` validates and authenticates
3. On success: guest cart is merged into authenticated user's cart via `CartService::mergeGuestCart()`
4. Session is regenerated for security
5. User is redirected to `/dashboard` which routes to:
   - `/admin` if `canAccessAdmin()` returns true (admin, store_admin, or staff)
   - `/my-account` for customers

### Social Login Flow (Google/Facebook)

1. User clicks social login button → redirected to OAuth provider
2. Provider authenticates → redirects back to `{APP_URL}/auth/{provider}/callback`
3. `SocialiteController` looks up user by provider ID, then by email, or creates new user
4. New users are created with role `customer`
5. Guest cart is merged, user is logged in

### Password Reset

> **Note:** Registration and password reset routes are currently **commented out** in `routes/auth.php`. These features are disabled in the current deployment.

The controllers exist (`PasswordResetLinkController`, `NewPasswordController`) but the routes must be uncommented to enable:
- `GET /forgot-password` - Shows reset request form
- `POST /forgot-password` - Sends reset link email
- `GET /reset-password/{token}` - Shows reset form
- `POST /reset-password` - Stores new password

### Registration

> **Note:** Customer self-registration is currently **disabled** (routes commented out). New customers are created via Social Login (Google/Facebook).

The `RegisteredUserController` exists and would create a user with role `customer`.

### Account Creation

- **Admin users**: Created directly in the database or via database seeder
- **Store Admin/Staff users**: Created by Admin via Admin Panel → Stores → Add User (sends welcome email with password)
- **Customers**: Created via Social Login (Google/Facebook)

### Session Management

| Setting | Value |
|---------|-------|
| **Session Driver** | `database` (stored in `sessions` table) |
| **Session Lifetime** | Configured via `SESSION_LIFETIME` env var (Laravel default: 120 minutes) |
| **Store Selection** | Stored in session as `active_store_id` |
| **Guest Cart** | Tracked by `session_id` in `carts` table |

### Credential Storage

| Credential Type | Storage Location |
|----------------|------------------|
| User passwords | Hashed in database (`users.password` column, bcrypt via Laravel) |
| API keys (PayPal, Stripe, etc.) | `.env` file (never committed to version control) |
| OAuth secrets | `.env` file |
| Store printer FTP passwords | Database (`stores.ftp_password` column) - stored as plaintext |
| Print Agent API key | `appsettings.json` on the store's Windows machine |

### Username Format

- All users log in with their **email address**
- Default seeded admin: `admin@qrinto.com`
- Default seeded customer: `customer@test.com`

---

## 4. User Roles

The system defines **four** user roles via the `users.role` enum column:

### 4.1 Admin (`admin`)

| Attribute | Detail |
|-----------|--------|
| **Description** | Super administrator with unrestricted access to the entire platform |
| **Responsibilities** | Platform management, all store oversight, user management, product catalog, orders across all stores, system configuration |
| **Accessible Modules** | Dashboard, All Stores, All Products, All Categories, All Product Types, All Orders, All Users, All Coupons, All Events, All Paper Types, All Templates, Qrinto Sizes, Custom Print Sizes, AI Product Generation, Store Statistics & Exports, Printing |
| **Restricted Modules** | None |

### 4.2 Store Administrator (`store_admin`)

| Attribute | Detail |
|-----------|--------|
| **Description** | Manager of a specific store, scoped to their assigned store only |
| **Responsibilities** | Managing their store's orders, products, categories, coupons, events, paper types, store details |
| **Accessible Modules** | Dashboard (own store), Own Store's Orders, Own Products, Own Categories, Own Coupons, Own Events, Own Paper Types, Store Details |
| **Restricted Modules** | Other stores' data, User management, Product Types, Qrinto Sizes, Custom Print Sizes, Store creation/deletion, Global settings, Store Statistics exports |

### 4.3 Staff (`staff`)

| Attribute | Detail |
|-----------|--------|
| **Description** | Store-level staff member with read access |
| **Responsibilities** | Viewing store orders |
| **Accessible Modules** | Can access admin panel (`canAccessAdmin()` returns true) |
| **Restricted Modules** | **Information Required** - Staff permissions are not explicitly differentiated from store_admin in the middleware; the `admin` middleware only checks `canAccessAdmin()`. Specific restrictions may be enforced at the controller/view level. |

### 4.4 Customer (`customer`)

| Attribute | Detail |
|-----------|--------|
| **Description** | End user who browses, customizes, and orders products |
| **Responsibilities** | Browsing products, designing custom prints, placing orders, tracking orders |
| **Accessible Modules** | Customer dashboard, Order history, Order tracking, Profile management, Product browsing, Customization, Cart, Checkout |
| **Restricted Modules** | Admin panel (blocked by `AdminMiddleware`) |

### Role Determination Methods (User Model)

```
isAdmin()        → role === 'admin'
isStoreAdmin()   → role === 'store_admin'
isStaff()        → role in ['staff', 'store_admin', 'admin']
canAccessAdmin() → role in ['admin', 'store_admin', 'staff']
isCustomer()     → role === 'customer'
```

---

## 5. Admin Portal Documentation

**URL:** `{APP_URL}/admin`  
**Middleware:** `auth`, `admin`  
**Layout:** `layouts.admin` with collapsible sidebar navigation

The Admin has **full, unrestricted access** to all modules across all stores.

### 5.1 Dashboard

**Route:** `GET /admin` → `Admin\DashboardController@index`

Displays:
- Total revenue (all stores or filtered by store for store_admin)
- Total orders count
- Order status distribution
- Recent orders
- Revenue trends

**Additional Dashboard Features:**
- `GET /admin/stores-summary` - JSON endpoint with store order statistics (paginated, sortable, date-filterable)
- `GET /admin/stores-summary/export` - CSV download of store order stats
- `GET /admin/store-statistics` - JSON endpoint with store entity counts (products, categories, etc.)
- `GET /admin/store-statistics/export` - CSV download of store entity statistics

### 5.2 Orders

**Routes:**
- `GET /admin/orders` → `Admin\OrderController@index`
- `GET /admin/orders/{order}` → `Admin\OrderController@show`
- `PUT /admin/orders/{order}/status` → `Admin\OrderController@updateStatus`
- `POST /admin/orders/{order}/duplicate` → `Admin\OrderController@duplicate`
- `GET /admin/orders/{order}/pdf` → `Admin\OrderController@show_pdf`
- `GET /admin/orders/{order}/realtime_pdf` → `Admin\OrderController@realtimePdf`

**Admin can:**
- View **all orders** across all stores
- Filter by status, payment status, search term
- Update order status: `pending` → `confirmed` → `processing` → `printing` → `shipped` → `delivered` / `delivered_store` (or `cancelled` / `refunded`)
- Update payment status
- Add admin notes
- Add tracking number
- View/generate print-ready PDF designs
- Duplicate orders (creates new order with `pending` status)
- Trigger status update email notifications to customers

**Order Printing:**
- `POST /admin/orders/{order}/print` → `Admin\OrderPrintController@sendPrint` - Creates a `PrintJob` record for the QrintoPrintAgent
- `GET /admin/orders/{order}/print-page` → `Admin\OrderPrintController@printPage` - Shows/redirects to printable design page

### 5.3 Products

**Routes:** Full CRUD resource at `/admin/products`

**Admin can:**
- **Create** products with: name, description, category, product type, paper type, event, store, prices (USD + CAD), images (frame, sample, background, overlay), SKU, tags, PDF orientation, no_of_pages (1=flat single, 2=flat double/mask, 4=folded), SEO metadata
- **Edit** any product regardless of which store created it
- **Delete** individual products or **bulk delete** multiple products
- **Publish/unpublish** products via `is_active` toggle
- **Manage product images** - upload, delete, set primary
- **Manage option groups** - add customizable options (size, finish, frame) with price modifiers (fixed, percentage, absolute)
- **Manage option values** - add/delete values within option groups
- **AI-powered generation** - `POST /admin/products/ai-generate` uses OpenRouter to auto-generate product descriptions, prices, SEO metadata, and attribute suggestions

**Mask Editor:**
- `GET /admin/products/{product}/mask` - Opens the mask editor for products with `no_of_pages` = 2 or 4
- `POST /admin/products/{product}/mask` - Saves mask data (JSON coordinates defining where customer photos are placed)

### 5.4 Categories

**Routes:** Full CRUD resource at `/admin/categories`

**Admin can:**
- Create categories with: name, description, image, parent category (for nesting), SEO metadata
- Edit any category
- Delete categories (cascade deletes products within the category)
- Organize categories in a parent-child tree hierarchy
- Categories are used to filter templates on the customer-facing template selection page

### 5.5 Product Types (Card Types / Sizes)

**Routes:** Full CRUD resource at `/admin/product-types`

**Admin can:**
- Create parent types (e.g., "Greeting Cards") - these serve as product categories in the customer flow
- Create child types (sizes) under parent types - these have specific prices, dimensions (width × height), and unit
- Configure: name, title, slug, icon (SVG), price, old/compare price, width, height, unit, sort order
- Each child type defines a size variant with its own pricing that overrides the product's base price

**Hierarchy:**
- Parent Type (no price/dimensions) = product category for customer flow
- Child Type (with price/dimensions) = selectable size variant

### 5.6 Coupons

**Routes:** Full CRUD resource at `/admin/coupons`

**Admin can:**
- Create coupons with: code, name, description, type (`percentage` or `fixed`), value, minimum order amount, maximum discount cap, usage limit, per-user usage limit, start/end dates
- **Apply globally** - admin-created coupons work across all stores
- Restrict coupons to specific stores (via `store_id`)
- Edit any coupon
- Delete any coupon

**Validation logic** (Coupon model `isValid()`):
- Must be active (`is_active = true`)
- Must be within date range (`starts_at` to `expires_at`)
- Must not have exceeded `usage_limit`
- Order amount must meet `min_order_amount` (currency-converted)

### 5.7 Events

**Routes:** Full CRUD resource at `/admin/events`

**Admin can:**
- Create events with: title, icon (SVG), color, start/end dates, active status
- Associate events with categories (many-to-many via `category_event` pivot)
- Associate events with stores (many-to-many via `event_store` pivot)
- Edit any event
- Delete any event
- Enable/disable events

### 5.8 Paper Types

**Routes:** CRUD (except show) at `/admin/paper-types`

**Admin can:**
- Create paper types with: title, active status
- Associate paper types with stores (many-to-many via `paper_type_store` pivot)
- Edit any paper type
- Delete any paper type

### 5.9 Stores

**Routes:** Full CRUD resource at `/admin/stores` + extras

**Admin can:**
- **Create stores** with: name, code (unique), owner name, email, phone, full address (street, city, state, zip, country), coordinates (lat/lon), opening/closing times, GST number, logo, notes
- **Configure printer settings**: printer IP, port, name, type, paper size
- **Configure FTP settings**: host, port, username, password, remote path, passive mode
- **Test FTP connection** - `POST /admin/stores/{id}/ftp-test`
- **Edit any store**
- **Toggle store active/inactive** status
- **Delete stores** (soft delete)
- **Manage store users** - create staff/store_admin users linked to the store (sends welcome email with auto-generated password)
- **Delete store users**
- **Search stores** (AJAX endpoint for print modal)

### 5.10 Templates (Design Templates)

**Routes:** Full CRUD resource at `/admin/templates` + extras

**Admin can:**
- Create templates with: name, slug, category, icon type (Lucide icon or uploaded image), canvas configuration (JSON), sort order
- Toggle template active/inactive status
- Upload template assets (AJAX)
- Edit any template
- Delete any template
- Assign templates to categories

### 5.11 Users

**Routes:**
- `GET /admin/users` → `Admin\UserController@index`
- `GET /admin/users/{user}` → `Admin\UserController@show`
- `PATCH /admin/users/{user}/toggle` → `Admin\UserController@toggleStatus`

**Admin can:**
- View all users across the platform
- Filter by search term and role
- View user details including order history and addresses
- Toggle user active/inactive status
- **Note:** User creation for admin/store_admin roles is done through the Stores module (Add User to Store)

### 5.12 Qrinto Sizes

**Routes:** Full CRUD resource at `/admin/qrinto-sizes`

Admin can manage the predefined sizes available for the Qrinto custom print flow (e.g., "Small 4×6", "Medium 5×7").

### 5.13 Custom Print Sizes

**Routes:** **Information Required** - Controller exists (`Admin\CustomPrintSizeController`) but no route registration found in `web.php`. May need to be added.

---

## 6. Store Portal Documentation

**URL:** `{APP_URL}/admin` (same panel, scoped by role)  
**Middleware:** `auth`, `admin`  
**Layout:** `layouts.admin` - displays store logo and store-specific branding

A Store Administrator accesses the **same admin panel** but sees only data belonging to their assigned store. The scoping is enforced at the **controller level** by checking `auth()->user()->isAdmin()` and filtering queries by `store_id`.

### 6.1 Dashboard

**Route:** `GET /admin` → `Admin\DashboardController@index`

Displays:
- Own store's revenue
- Own store's order count
- Own store's order status distribution
- Own store's recent orders

**Not available to Store Admin:**
- Store summary/statistics across all stores
- Export functionality for cross-store reports

### 6.2 Orders

**Route:** `GET /admin/orders` → `Admin\OrderController@index` (filtered by `store_id`)

**Store Admin can:**
- View only **their own store's orders**
- Update order status (triggers customer email notification)
- Update payment status
- Add admin notes
- Add tracking number
- View/download print-ready PDF designs
- Send print jobs to the store's printer
- Duplicate orders
- Search and filter orders

**Store Admin cannot:**
- View orders from other stores
- Access cross-store order reports

### 6.3 Products

**Route:** `/admin/products` (filtered by `created_by` or `store_id`)

**Store Admin can:**
- Create products for their store
- Edit their own products
- Delete their own products
- View their own products
- Configure mask editor for their products
- Manage option groups and values on their products

**Store Admin cannot:**
- View, edit, or delete products created by other stores
- Create products assigned to other stores
- Bulk delete products from other stores

### 6.4 Categories

**Route:** `/admin/categories` (filtered by `user_id`)

**Store Admin can:**
- Create categories (tagged with their `user_id`)
- Edit their own categories
- Delete their own categories

**Store Admin cannot:**
- View, edit, or delete categories created by other users/stores

### 6.5 Coupons

**Route:** `/admin/coupons` (filtered by `user_id`)

**Store Admin can:**
- Create coupons for their store (tagged with `user_id` and optionally `store_id`)
- Edit their own coupons
- Delete their own coupons

**Store Admin cannot:**
- View, edit, or delete coupons created by other stores or by admin

### 6.6 Events

**Route:** `/admin/events` (scoped)

**Store Admin can:**
- Create events (tagged with `created_by`)
- Associate events with categories
- Associate events with their own store
- Edit their own events
- Delete their own events

**Store Admin cannot:**
- View, edit, or delete events created by other users

### 6.7 Paper Types

**Route:** `/admin/paper-types` (filtered by `user_id`)

**Store Admin can:**
- Create paper types (tagged with `user_id`)
- Associate with their own store
- Edit their own paper types
- Delete their own paper types

**Store Admin cannot:**
- View, edit, or delete paper types created by other users

### 6.8 Store Details

Store Admin can update their own store's information through the store edit form:

- Store name and code
- Owner name
- Contact information (email, phone)
- Address (street, city, state, zip, country)
- Business hours (opening/closing time)
- GST number
- Logo upload
- Notes
- Printer settings (IP, port, name, type, paper size)
- FTP configuration (host, port, username, password, path, passive mode)

**Store Admin cannot:**
- Create new stores
- Delete their store
- Access other stores' settings

---

## 7. Permission Matrix

### 7.1 Feature Comparison

| Feature | Admin | Store Admin |
|---------|:-----:|:-----------:|
| **Stores** | | |
| View all stores | ✅ | ❌ |
| View own store | ✅ | ✅ |
| Create stores | ✅ | ❌ |
| Edit any store | ✅ | ❌ |
| Edit own store | ✅ | ✅ |
| Delete stores | ✅ | ❌ |
| Toggle store status | ✅ | ❌ |
| **Products** | | |
| Manage all products | ✅ | ❌ |
| Create own products | ✅ | ✅ |
| Edit own products | ✅ | ✅ |
| Delete own products | ✅ | ✅ |
| Bulk delete products | ✅ | ❌ (own only) |
| Mask editor | ✅ | ✅ (own products) |
| AI product generation | ✅ | ✅ |
| **Categories** | | |
| Manage all categories | ✅ | ❌ |
| Create own categories | ✅ | ✅ |
| Edit own categories | ✅ | ✅ |
| Delete own categories | ✅ | ✅ |
| **Product Types** | | |
| Create/edit/delete product types | ✅ | ❌ |
| **Orders** | | |
| View all orders | ✅ | ❌ |
| View own store orders | ✅ | ✅ |
| Update order status | ✅ | ✅ (own store) |
| Update payment status | ✅ | ✅ (own store) |
| Duplicate orders | ✅ | ✅ (own store) |
| Print orders | ✅ | ✅ (own store) |
| View PDF designs | ✅ | ✅ (own store) |
| **Coupons** | | |
| Manage all coupons | ✅ | ❌ |
| Create own coupons | ✅ | ✅ |
| Edit own coupons | ✅ | ✅ |
| Delete own coupons | ✅ | ✅ |
| Apply globally | ✅ | ❌ |
| **Events** | | |
| Manage all events | ✅ | ❌ |
| Create own events | ✅ | ✅ |
| Edit own events | ✅ | ✅ |
| Delete own events | ✅ | ✅ |
| **Paper Types** | | |
| Manage all paper types | ✅ | ❌ |
| Create own paper types | ✅ | ✅ |
| Edit own paper types | ✅ | ✅ |
| Delete own paper types | ✅ | ✅ |
| **Users** | | |
| View all users | ✅ | ❌ |
| Create store users | ✅ | ❌ |
| Toggle user status | ✅ | ❌ |
| **Templates** | | |
| Manage design templates | ✅ | **Information Required** |
| **Analytics** | | |
| Full dashboard analytics | ✅ | Limited (own store) |
| Store summary reports | ✅ | ❌ |
| Export CSV reports | ✅ | ❌ |
| **System** | | |
| Qrinto sizes management | ✅ | ❌ |
| Custom print sizes | ✅ | ❌ |
| Global settings | ✅ | ❌ |

### 7.2 Permission Hierarchy

```
Admin (Full Access)
  └── Can do everything Store Admin can do, PLUS:
      ├── Access all stores' data
      ├── Create/delete stores
      ├── Manage users across the platform
      ├── Manage product types (global catalog structure)
      ├── View cross-store analytics and exports
      ├── Manage design templates
      └── Configure global sizes (Qrinto, Custom Print)

Store Admin (Scoped Access)
  └── Limited to their assigned store:
      ├── Manage own store's orders
      ├── Create/edit/delete own products, categories, coupons, events, paper types
      ├── Update own store details and printer configuration
      └── View own store's dashboard statistics

Staff (Read Access)
  └── Can access admin panel (canAccessAdmin() = true)
      └── Specific permissions: Information Required
          (Controller-level enforcement not fully differentiated from store_admin)

Customer (No Admin Access)
  └── Blocked by AdminMiddleware
      ├── Browse and order products
      ├── Track orders
      └── Manage own profile
```

---

## 8. Customer Website

### 8.1 Customer Flows

The customer experience has **two variants**:
- **Mobile Flow** (`/`) - Optimized for kiosk/mobile via `QuickFlowController`, uses `layouts.quick-flow`
- **Desktop Flow** (`/pc/`) - Desktop-optimized via `QuickFlowPcController`, uses `layouts.quick-flow-pc`

Both flows share identical business logic (PC controller extends mobile controller).

### 8.2 Store Finding

**Route:** `GET /find-store`

- **Geolocation:** Uses browser `navigator.geolocation` to get user's coordinates
- **Search:** Text query sent to OpenStreetMap Nominatim API for geocoding
- **Proximity:** Uses Haversine formula in SQL to find stores within 300 miles
- **QR Scan:** Scanning store QR code (`/store/{storeCode}`) auto-selects the store
- Selected store saved in session as `active_store_id`

### 8.3 Product Browsing

1. **Select Product Type** (`GET /`) - Shows parent product types (e.g., Greeting Cards, Magnets)
2. **Select Size** (`GET /type/{type:slug}`) - Shows child types (sizes) with prices under the selected parent
3. **Browse Templates** - Shows design templates filtered by category, with event-based filtering

### 8.4 Product Customization

**Route:** `GET /customize/{product:slug}`

The customization experience varies by `no_of_pages`:

| `no_of_pages` | Type | View | Description |
|---------------|------|------|-------------|
| `1` | Flat Single | `customize-single.blade.php` | Full image upload, no masks |
| `2` | Flat Double (Mask) | `customize-single-mask.blade.php` | Photo placed within defined mask zones |
| `4` | Folded Card | `customize-double.blade.php` | Four pages: Frame (front), Sample (inside left), Background (inside right), Overlay (back) |

Features:
- Image upload via AJAX (`POST /upload`)
- Composite image upload (`POST /upload-composite`)
- Canvas-based mask positioning (Fabric.js)
- 30 custom fonts available for text overlays
- Real-time design preview

### 8.5 Shopping Cart

**Routes:** `/cart/*`

- **Multi-item cart** - supports adding multiple products
- **Guest cart** - tracked by session ID, merged on login
- **Quantity adjustment** - update quantities per item
- **Coupon application** - apply/remove promo codes
- **Cart count** - AJAX endpoint for header badge (`GET /cart/count`)

### 8.6 Checkout

**Two checkout modes:**

1. **Single-item checkout** (`POST /checkout`) - Direct checkout for one product
2. **Cart checkout** (`GET /cart-checkout`) - Checkout for multiple cart items

**Payment options:**
- **PayPal** - Create order → capture payment → create internal order
- **Cash at Counter** - Creates order with `payment_status: 'pending'`
- **Stripe** - **Information Required** - Stripe integration exists in `PaymentService` but checkout UI integration details need verification
- **Razorpay** - **Information Required** - Razorpay integration exists in `PaymentService` but checkout UI integration details need verification

**Checkout fields:**
- Pickup name, email, phone
- Special instructions
- Promo code
- Terms acceptance

### 8.7 Order Confirmation

**Route:** `GET /confirmation/{order}`

Displays:
- Order number and status
- Product details with design preview
- Store pickup information
- 3D flip preview of the design

**Emails sent on order creation:**
- `OrderConfirmationMail` → Customer
- `AdminOrderAlertMail` → Admin
- `StoreOrderAlertMail` → Store email
- `QuickFlowOrderMail` → Customer + Admin + Store (with PDF attachment for Quick Flow orders)

### 8.8 Order Tracking

**Routes:**
- `GET /track` - Tracking form
- `POST /track` - Submit order number
- `GET /track/{orderNumber}` - Tracking results

Displays:
- Order status with timeline
- Product details
- Store pickup information
- Status history with timestamps
- Design preview

### 8.9 Customer Account

**Routes (authenticated):**
- `GET /my-account` → `CustomerController@dashboard` - Dashboard with order stats
- `GET /my-account/orders` → `CustomerController@orders` - Paginated order list
- `GET /my-account/orders/{order}` → `CustomerController@orderDetail` - Order detail (owner check enforced)

### 8.10 Profile Management

**Routes (authenticated):**
- `GET /profile` → Profile edit form
- `PATCH /profile` → Update name/email (resets email verification if email changes)
- `DELETE /profile` → Delete account (requires password confirmation)

### 8.11 Custom Print (Direct Upload)

**Route:** `GET /custom-print`

Allows customers to skip template selection and upload their own design file:
1. Select size → quantity
2. Upload custom design file
3. Checkout (PayPal or cash)

---

## 9. Customer Resource Center

### Current State

> **Information Required** - No dedicated Customer Resource Center module was found in the codebase. The following related resources exist:

| Resource | Location | Description |
|----------|----------|-------------|
| **Terms & Privacy** | `public/Qrinto_Terms_and_Privacy_Notice.pdf` | Legal document accessible to customers |
| **Onboarding Tour** | `public/js/qrinto-tour.js` + `public/css/qrinto-tour.css` | Interactive guided tour using Driver.js for first-time users |
| **PWA Support** | `public/manifest.json` + `public/sw.js` | Progressive Web App with offline capabilities |

### Documentation Gaps

The following areas require dedicated customer-facing documentation:

- [ ] User manuals for product customization
- [ ] Video tutorials for the design editor
- [ ] FAQ section
- [ ] Downloadable design guides (PDFs)
- [ ] Training materials for store staff
- [ ] Documentation for Events feature
- [ ] Documentation for Paper Types feature
- [ ] Documentation for newly introduced features (AI product generation, Noritsu editor)

---

## 10. Database Overview

### 10.1 Main Tables

The database contains **30+ tables** across 62 migrations:

#### Core Business Tables

| Table | Description | Key Columns |
|-------|-------------|-------------|
| `users` | All system users (admin, store_admin, staff, customer) | id, name, email, role, store_id, google_id, facebook_id |
| `stores` | Physical store locations | id, store_name, store_code, address, printer config, FTP config |
| `products` | Design templates / printable products | id, name, slug, category_id, product_type_id, base_price, mask_data, no_of_pages |
| `product_images` | Multiple images per product | id, product_id, image_path, is_primary |
| `product_types` | Product categories and size variants (parent-child) | id, parent_id, name, price, width, height |
| `categories` | Product categorization (parent-child tree) | id, parent_id, user_id, name, slug |
| `templates` | Design templates with canvas configuration | id, name, category_id, canvas_config (JSON) |
| `events` | Seasonal/promotional events | id, title, icon_svg, color, start_date, end_date |
| `paper_types` | Available paper options | id, title, user_id |

#### Order & Commerce Tables

| Table | Description | Key Columns |
|-------|-------------|-------------|
| `orders` | Customer orders | id, order_number, user_id, store_id, status, total, payment_status, flow_data |
| `order_items` | Individual items within orders | id, order_id, product_id, quantity, unit_price, uploaded_images, pdf_path |
| `order_status_histories` | Audit trail of status changes | id, order_id, old_status, new_status, notes |
| `carts` | Shopping carts (guest + authenticated) | id, user_id, session_id, coupon_id |
| `cart_items` | Items in cart | id, cart_id, product_id, quantity, unit_price |
| `payments` | Payment transaction records | id, order_id, gateway, amount, status, gateway_response |
| `coupons` | Discount codes | id, code, type, value, usage_limit, user_id, store_id |
| `addresses` | Customer addresses | id, user_id, address_line_1, city, state, country |

#### Product Configuration Tables

| Table | Description |
|-------|-------------|
| `product_option_groups` | Option groups per product (e.g., Size, Finish) |
| `product_option_values` | Values within option groups with price modifiers |
| `custom_print_sizes` | Predefined sizes for custom print flow |
| `qrinto_sizes` | Predefined sizes for Qrinto print flow |

#### Print System Tables

| Table | Description |
|-------|-------------|
| `print_jobs` | Print job queue for QrintoPrintAgent |
| `print_logs` | Print attempt audit trail |

#### User & Upload Tables

| Table | Description |
|-------|-------------|
| `customer_uploads` | Customer-uploaded images during customization |
| `user_designs` | Saved user designs with canvas data |

#### Pivot Tables (Many-to-Many)

| Table | Connects |
|-------|----------|
| `category_event` | Events ↔ Categories |
| `event_store` | Events ↔ Stores |
| `paper_type_store` | Paper Types ↔ Stores |

#### Framework Tables

| Table | Purpose |
|-------|---------|
| `sessions` | Database session storage |
| `cache` / `cache_locks` | Database cache driver |
| `jobs` / `job_batches` / `failed_jobs` | Queue system |
| `password_reset_tokens` | Password reset tokens |

### 10.2 Entity Relationship Diagram

```
┌──────────┐     ┌──────────────┐     ┌──────────┐
│  users   │────<│   orders     │>────│  stores  │
│          │     │              │     │          │
│ role     │     │ order_number │     │store_code│
│ store_id─┼─────│ status       │     │ printer  │
└──────┬───┘     │ total        │     │ FTP conf │
       │         └──────┬───────┘     └────┬─────┘
       │                │                   │
       │         ┌──────┴───────┐          │
       │         │ order_items  │          │
       │         │              │          │
       │         │ product_id   │          │
       │         │ quantity     │          │
       │         │ uploaded_imgs│          │
       │         └──────┬───────┘          │
       │                │                   │
       │         ┌──────┴───────┐          │
       │         │  products    │          │
       │         │              │>─────────┘
       │         │ category_id  │
       │         │ prod_type_id │
       │         │ mask_data    │
       │         │ no_of_pages  │
       │         └──┬───┬───┬───┘
       │            │   │   │
       │  ┌─────────┘   │   └─────────┐
       │  │             │             │
  ┌────┴──┴───┐  ┌──────┴──────┐  ┌──┴──────────┐
  │categories │  │product_types│  │product_images│
  │           │  │             │  └──────────────┘
  │ parent_id │  │ parent_id   │
  │ (tree)    │  │ price       │  ┌──────────────────┐
  └───────────┘  │ width/height│  │product_opt_groups│
                 └─────────────┘  │                  │
                                  │ display_type     │
  ┌───────────┐                   └────────┬─────────┘
  │  coupons  │                            │
  │           │                   ┌────────┴─────────┐
  │ code      │                   │product_opt_values│
  │ type      │                   │                  │
  │ value     │                   │ price_modifier   │
  └───────────┘                   └──────────────────┘

  ┌───────────┐    ┌──────────┐    ┌──────────────┐
  │  events   │──<>│ cat_event│<>──│  categories  │
  │           │    └──────────┘    └──────────────┘
  │           │    ┌──────────┐
  │           │──<>│evt_store │<>──┌──────────┐
  └───────────┘    └──────────┘    │  stores  │
                                   └──────────┘
  ┌───────────┐    ┌──────────────┐
  │paper_types│──<>│paper_type_str│<>──stores
  └───────────┘    └──────────────┘

  ┌───────────┐    ┌───────────┐
  │print_jobs │    │print_logs │
  │           │    │           │
  │ order_id  │    │ order_id  │
  │ store_id  │    │ store_id  │
  │ asset_url │    │ status    │
  │ media_size│    │ printed_at│
  └───────────┘    └───────────┘
```

### 10.3 Key Design Patterns

- **Soft Deletes:** Used on `products`, `orders`, `stores`
- **JSON Columns:** Extensive use for `customization_data`, `selected_options`, `uploaded_images`, `mask_data`, `flow_data`, `canvas_config`, `gateway_response`, `settings`
- **Dual Currency:** Products, option values, product types, and size tables carry both `price` and `price_cad` columns
- **Guest Support:** `orders.user_id` and `cart_items.product_id` are nullable to support guest checkout and deleted products
- **Self-Referential Trees:** Categories and Product Types use `parent_id` for parent-child hierarchy
- **Audit Trail:** `order_status_histories` logs every status change with user attribution

### 10.4 Important Models

| Model | File | Key Features |
|-------|------|--------------|
| `User` | `app/Models/User.php` | Role checking methods, social login IDs, store relationship |
| `Product` | `app/Models/Product.php` | Slug routing, mask data, image accessors, option pricing calculation, aspect ratio/print dimensions |
| `Order` | `app/Models/Order.php` | Auto-generated order/invoice numbers, status colors, flow data |
| `Store` | `app/Models/Store.php` | Currency detection, printer connectivity check, FTP testing |
| `Cart` | `app/Models/Cart.php` | Computed subtotal/discount/total, session-based guest tracking |
| `Coupon` | `app/Models/Coupon.php` | Validation logic, discount calculation |
| `PrintJob` | `app/Models/PrintJob.php` | Agent communication format mapping (`toAgentArray()`) |
| `CurrencyService` | `app/Services/CurrencyService.php` | Static utility, hardcoded 1.42 CAD rate |

---

## 11. API Documentation

### 11.1 Authentication

- **Web routes:** Session-based authentication via Laravel Breeze
- **API routes:** Currently **no API authentication** (no sanctum/passport tokens). API endpoints are open.
- **Print Agent:** Authenticates via `store_id` query parameter (no token-based auth on the polling endpoint)

### 11.2 API Groups

#### Product API (`/api/v1`)

| Method | Endpoint | Description | Request | Response |
|--------|----------|-------------|---------|----------|
| POST | `/api/v1/products/{product}/calculate-price` | Calculate product price with options | `{ selected_options: [id, ...] }` | `{ price, formatted_price, breakdown }` |
| POST | `/api/v1/upload` | Upload customer image | Multipart: `image` file | `{ id, url, thumbnail_url, width, height }` |
| DELETE | `/api/v1/upload/{upload}` | Delete uploaded image | - | `{ success }` |

#### Cart API (`/api/v1/cart`)

| Method | Endpoint | Description | Request | Response |
|--------|----------|-------------|---------|----------|
| POST | `/api/v1/cart/add` | Add item to cart | `{ product_id, quantity, unit_price, customization_data, selected_options }` | `{ success, cart, item_count }` |
| PATCH | `/api/v1/cart/update/{itemId}` | Update item quantity | `{ quantity }` | `{ success, cart }` |
| DELETE | `/api/v1/cart/remove/{itemId}` | Remove item from cart | - | `{ success, cart }` |
| POST | `/api/v1/cart/coupon` | Apply coupon code | `{ code }` | `{ success, message, discount }` |
| DELETE | `/api/v1/cart/coupon` | Remove applied coupon | - | `{ success }` |

#### Noritsu API (`/api/noritsu/v1`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/noritsu/v1/config` | Session configuration (upload limits) |
| GET | `/api/noritsu/v1/stores` | List active stores |
| GET | `/api/noritsu/v1/stores/{id}` | Store details |
| GET | `/api/noritsu/v1/templates` | List products as templates (with mask data) |
| GET | `/api/noritsu/v1/templates/{id}` | Template/product detail |
| POST | `/api/noritsu/v1/orders/session` | Start ordering session |
| POST | `/api/noritsu/v1/orders` | Create order (with 8% tax, $5.99 shipping) |
| GET | `/api/noritsu/v1/orders/{id}` | Order status and items |

#### Print Agent API (`/api`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/print-jobs/pending?store_id={id}` | Poll for pending print jobs (marks as `queued`) |
| POST | `/api/agent/messages` | Agent sends status updates (`completed`, `failed`, etc.) |

### 11.3 Response Format

Standard Laravel JSON responses:
```json
{
  "success": true,
  "message": "Operation completed",
  "data": { ... }
}
```

Error responses:
```json
{
  "success": false,
  "message": "Error description",
  "errors": { "field": ["Validation message"] }
}
```

### 11.4 Error Handling

- **Validation errors:** HTTP 422 with field-level error messages
- **Authentication errors:** HTTP 401 / redirect to login
- **Authorization errors:** HTTP 403 (via `AdminMiddleware`)
- **Not found:** HTTP 404 (custom `errors/404.blade.php`)
- **Server errors:** HTTP 500 with Laravel exception handler

---

## 12. Development Environment

### 12.1 Prerequisites

- PHP 8.2+
- Composer 2.x
- Node.js 18+ and npm
- MySQL 8.0+ (or SQLite for development)
- Git

### 12.2 Clone & Setup

```bash
# Clone repository
git clone <repository-url> qrinto
cd qrinto

# Quick setup (installs PHP + Node deps, generates key, runs migrations, builds assets)
composer setup
```

The `composer setup` script runs:
1. `composer install` - Install PHP dependencies
2. `php artisan key:generate` - Generate application encryption key
3. `php artisan migrate` - Run database migrations
4. `npm install` - Install Node.js dependencies
5. `npm run build` - Build frontend assets with Vite

### 12.3 Environment Variables

Copy `.env.example` to `.env` and configure:

```bash
cp .env.example .env
```

**Required variables:**

| Variable | Description | Example |
|----------|-------------|---------|
| `APP_NAME` | Application name | `Qrinto` |
| `APP_ENV` | Environment | `local` |
| `APP_KEY` | Encryption key | Auto-generated by `key:generate` |
| `APP_DEBUG` | Debug mode | `true` (local) |
| `APP_URL` | Application URL | `http://localhost:8000` |
| `DB_CONNECTION` | Database driver | `mysql` or `sqlite` |
| `DB_HOST` | Database host | `127.0.0.1` |
| `DB_PORT` | Database port | `3306` |
| `DB_DATABASE` | Database name | `qrinto` |
| `DB_USERNAME` | Database user | `root` |
| `DB_PASSWORD` | Database password | *(your password)* |
| `PAYMENT_GATEWAY` | Active payment gateway | `paypal` / `razorpay` / `stripe` |
| `PAYPAL_MODE` | PayPal environment | `sandbox` / `live` |
| `PAYPAL_CLIENT_ID` | PayPal client ID | *(from PayPal dashboard)* |
| `PAYPAL_SECRET` | PayPal secret | *(from PayPal dashboard)* |
| `MAIL_MAILER` | Mail driver | `smtp` / `log` |

**Additional variables (not in .env.example, add manually if needed):**

| Variable | Description |
|----------|-------------|
| `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` / `GOOGLE_REDIRECT_URI` | Google OAuth |
| `FACEBOOK_CLIENT_ID` / `FACEBOOK_CLIENT_SECRET` / `FACEBOOK_REDIRECT_URI` | Facebook OAuth |
| `RAZORPAY_KEY_ID` / `RAZORPAY_KEY_SECRET` | Razorpay payment |
| `STRIPE_KEY` / `STRIPE_SECRET` | Stripe payment |
| `OPENROUTER_API_KEY` / `OPENROUTER_MODEL` | AI integration |

### 12.4 Database Setup

```bash
# Create database (MySQL)
mysql -u root -p -e "CREATE DATABASE qrinto;"

# Run migrations
php artisan migrate

# Seed initial data (admin user, sample products, stores)
php artisan db:seed
```

**Seeded data includes:**
- Admin user: `admin@qrinto.com` (password: check seeder)
- Test customer: `customer@test.com`
- 6 product categories with 8 sample products
- 8 sample stores with printer configurations
- 2 sample coupons (WELCOME10, FLAT200)

### 12.5 Storage Link

```bash
php artisan storage:link
```

Creates a symlink from `public/storage` to `storage/app/public`.

### 12.6 Queue Configuration

```bash
# Queue driver is set to 'database' by default
# Start the queue worker for print jobs and emails
php artisan queue:listen
```

### 12.7 Build Assets

```bash
# Development (with HMR)
npm run dev

# Production build
npm run build
```

**Vite entry points:**
- `resources/css/app.css` - Tailwind CSS
- `resources/js/app.js` - Alpine.js + Lucide icons
- `resources/js/noritsu.jsx` - React Noritsu editor

### 12.8 Local Development

```bash
# Start everything at once (server + queue + logs + Vite)
composer dev
```

This runs concurrently:
1. `php artisan serve` - Laravel dev server on `http://localhost:8000`
2. `php artisan queue:listen` - Queue worker
3. `php artisan pail` - Real-time log viewer
4. `npm run dev` - Vite dev server with HMR

### 12.9 Deployment

**Information Required** - No deployment configuration (CI/CD pipeline, Docker, deployment scripts) was found in the codebase. Based on the presence of `.htaccess` files and FTP references, the application appears to be deployed to shared hosting (cPanel).

**Deployment checklist:**
1. `composer install --no-dev --optimize-autoloader`
2. `npm install && npm run build`
3. `php artisan migrate --force`
4. `php artisan config:cache`
5. `php artisan route:cache`
6. `php artisan view:cache`
7. `php artisan storage:link`
8. Set `APP_ENV=production`, `APP_DEBUG=false`
9. Configure queue worker (supervisor or cPanel cron)

---

## 13. Application Workflow

### 13.1 Customer Order Flow

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  Scan QR /  │     │   Select    │     │   Select    │
│ Find Store  │────>│ Product Type│────>│    Size     │
│             │     │ (Parent)    │     │  (Child)    │
└─────────────┘     └─────────────┘     └──────┬──────┘
                                               │
                    ┌─────────────┐     ┌──────┴──────┐
                    │  Customize  │<────│   Browse    │
                    │   Design    │     │  Templates  │
                    └──────┬──────┘     └─────────────┘
                           │
              ┌────────────┴────────────┐
              │                         │
       ┌──────┴──────┐          ┌──────┴──────┐
       │  Add to     │          │   Direct    │
       │   Cart      │          │  Checkout   │
       └──────┬──────┘          └──────┬──────┘
              │                        │
       ┌──────┴──────┐                 │
       │    Cart     │                 │
       │  Checkout   │                 │
       └──────┬──────┘                 │
              └────────────┬───────────┘
                           │
              ┌────────────┴────────────┐
              │                         │
       ┌──────┴──────┐          ┌──────┴──────┐
       │   PayPal    │          │   Cash at   │
       │  Payment    │          │  Counter    │
       └──────┬──────┘          └──────┬──────┘
              │                        │
              └────────────┬───────────┘
                           │
                    ┌──────┴──────┐
                    │   Order     │
                    │ Confirmation│
                    └──────┬──────┘
                           │
                    ┌──────┴──────┐
                    │   Emails    │
                    │   Sent     │
                    │ (Customer, │
                    │ Admin,Store)│
                    └─────────────┘
```

### 13.2 Order Status Lifecycle

```
  pending ──> confirmed ──> processing ──> printing ──> shipped
     │                                        │           │
     │                                        v           v
     │                                   delivered   delivered_store
     │
     └──> cancelled
     └──> refunded
```

### 13.3 Print Job Flow

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│ Admin clicks │     │  PrintJob    │     │  Agent polls  │
│ "Print" on   │────>│  created in  │────>│  GET /api/    │
│   order      │     │  DB (pending)│     │  print-jobs/  │
└──────────────┘     └──────────────┘     │  pending      │
                                          └──────┬───────┘
                                                 │
┌──────────────┐     ┌──────────────┐     ┌──────┴───────┐
│ Print sent   │     │   Agent      │     │   Agent      │
│ to Noritsu   │<────│  executes    │<────│  downloads   │
│   printer    │     │  print       │     │  asset file  │
└──────┬───────┘     └──────────────┘     └──────────────┘
       │
┌──────┴───────┐     ┌──────────────┐
│ Agent reports│────>│  PrintJob    │
│  status back │     │  updated     │
│  via POST    │     │  (completed/ │
│              │     │   failed)    │
└──────────────┘     └──────────────┘
```

### 13.4 Payment Flow (PayPal)

```
┌──────────┐    ┌──────────────┐    ┌──────────────┐
│ Customer │    │ POST /paypal │    │  PayPal API  │
│ clicks   │───>│ /create      │───>│  Create Order│
│ "PayPal" │    └──────────────┘    └──────┬───────┘
└──────────┘                               │
                                    ┌──────┴───────┐
                                    │ PayPal order │
┌──────────┐    ┌──────────────┐    │ ID returned  │
│ Customer │    │ PayPal SDK   │<───└──────────────┘
│ approves │───>│ popup shows  │
│ payment  │    └──────┬───────┘
└──────────┘           │
                ┌──────┴───────┐    ┌──────────────┐
                │ POST /paypal │    │  PayPal API  │
                │ /capture     │───>│ Capture      │
                └──────────────┘    └──────┬───────┘
                                           │
                ┌──────────────┐    ┌──────┴───────┐
                │ Internal     │<───│ Payment      │
                │ Order created│    │ confirmed    │
                └──────────────┘    └──────────────┘
```

### 13.5 Email Notifications

| Trigger | Email Class | Recipient | Attachment |
|---------|------------|-----------|------------|
| New order placed | `OrderConfirmationMail` | Customer | - |
| New order placed | `AdminOrderAlertMail` | Admin | - |
| New order placed | `StoreOrderAlertMail` | Store email | - |
| Quick Flow order | `QuickFlowOrderMail` | Customer + Admin + Store | PDF design |
| Order status changed | `OrderStatusUpdateMail` | Customer | - |
| Store user created | `StoreUserWelcomeMail` | New store user | - (includes password) |

---

## 14. Folder Structure

```
qrinto/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/                    # Admin panel controllers (16 files)
│   │   │   │   ├── AIProductController   # AI-powered product generation
│   │   │   │   ├── CategoryController    # Category CRUD
│   │   │   │   ├── CouponController      # Coupon CRUD
│   │   │   │   ├── CustomPrintSizeController # Custom print size CRUD
│   │   │   │   ├── DashboardController   # Admin dashboard + analytics
│   │   │   │   ├── EventController       # Event CRUD
│   │   │   │   ├── OrderController       # Order management
│   │   │   │   ├── OrderPrintController  # Print job creation
│   │   │   │   ├── PaperTypeController   # Paper type CRUD
│   │   │   │   ├── ProductController     # Product CRUD + masks + options
│   │   │   │   ├── ProductTypeController # Product type hierarchy CRUD
│   │   │   │   ├── QrintoSizeController  # Qrinto size CRUD
│   │   │   │   ├── StoreController       # Store CRUD + user management
│   │   │   │   ├── TemplateController    # Design template CRUD
│   │   │   │   └── UserController        # User listing + toggle
│   │   │   ├── Api/                      # API controllers
│   │   │   │   ├── AgentController       # Print agent polling/messaging
│   │   │   │   ├── UserDesignController  # Save user designs
│   │   │   │   └── Noritsu/             # Noritsu app API
│   │   │   │       ├── OrderController
│   │   │   │       ├── StoreController
│   │   │   │       └── TemplateController
│   │   │   ├── Auth/                     # Authentication controllers (Breeze)
│   │   │   │   ├── AuthenticatedSessionController
│   │   │   │   ├── SocialiteController   # Google/Facebook OAuth
│   │   │   │   └── ... (password, email verification, etc.)
│   │   │   ├── AIController              # AI chat endpoint
│   │   │   ├── CartController            # Shopping cart
│   │   │   ├── CheckoutController        # Checkout process
│   │   │   ├── CustomerController        # Customer account
│   │   │   ├── CustomPrintController     # Direct upload custom prints
│   │   │   ├── HomeController            # Landing page
│   │   │   ├── NoritsuController         # Noritsu editor views
│   │   │   ├── ProductController         # Public product browsing
│   │   │   ├── ProfileController         # Profile management
│   │   │   ├── QrintoController          # Qrinto print flow
│   │   │   ├── QuickFlowController       # Main customer flow (mobile)
│   │   │   ├── QuickFlowPcController     # Customer flow (desktop, extends mobile)
│   │   │   ├── StoreQrController         # QR code scanning + display
│   │   │   ├── UploadController          # Image upload API
│   │   │   └── UtilityController         # Thumbnail generation
│   │   └── Middleware/
│   │       └── AdminMiddleware           # Checks canAccessAdmin()
│   ├── Jobs/
│   │   └── SendPrintJob                  # Legacy FTP print job (disabled)
│   ├── Mail/
│   │   ├── AdminOrderAlertMail
│   │   ├── OrderConfirmationMail
│   │   ├── OrderStatusUpdateMail
│   │   ├── QuickFlowOrderMail
│   │   ├── StoreOrderAlertMail
│   │   └── StoreUserWelcomeMail
│   ├── Models/                           # 25 Eloquent models
│   │   ├── Address, Cart, CartItem
│   │   ├── Category, Coupon, CustomPrintSize
│   │   ├── CustomerUpload, Event
│   │   ├── Order, OrderItem, OrderStatusHistory
│   │   ├── PaperType, Payment
│   │   ├── PrintJob, PrintLog
│   │   ├── Product, ProductImage
│   │   ├── ProductOptionGroup, ProductOptionValue
│   │   ├── ProductType, QrintoSize
│   │   ├── Store, Template
│   │   ├── User, UserDesign
│   │   └── (no Providers directory - uses default)
│   └── Services/
│       ├── CartService                   # Cart lifecycle management
│       ├── CurrencyService               # USD/CAD conversion (static, rate=1.42)
│       ├── OpenRouterService             # AI/LLM integration
│       └── PaymentService                # Multi-gateway payment processor
├── config/                               # Laravel configuration
│   ├── app.php, auth.php, cache.php
│   ├── database.php                      # DB connections (MySQL/SQLite/PostgreSQL)
│   ├── filesystems.php                   # Local + S3 storage disks
│   ├── mail.php, queue.php, session.php
│   └── services.php                      # All third-party service configs
├── database/
│   ├── migrations/                       # 62 migration files
│   └── seeders/
│       ├── DatabaseSeeder                # Admin + products + categories + coupons
│       ├── StoreSeeder                   # 8 sample stores
│       ├── TemplateSeeder                # 2 sample templates (may be outdated)
│       └── UpdateCategoriesSeeder        # Replaces categories with Greeting Cards/Magnets
├── docs/                                 # Documentation
│   ├── custom_print.sql, qrinto.sql      # SQL schema dumps
│   ├── landscape.pdf, portrait.pdf       # PDF samples
│   ├── prompts.txt                       # AI prompt templates
│   ├── noritsu/                          # Noritsu subsystem docs
│   │   ├── ARCHITECTURE.md, DATA_MODEL.md
│   │   ├── ROADMAP.md, SCREENS.md
│   └── openroute/                        # OpenRouter AI docs
├── public/
│   ├── agent/QrintoPrintAgent/           # .NET 8 C# print agent
│   │   ├── Program.cs, QrintoPrintAgent.csproj
│   │   ├── appsettings.json
│   │   └── src/
│   │       ├── Config/AgentConfig.cs
│   │       ├── Core/PrintAgentWorker.cs
│   │       ├── Models/                   # PrintJob, PrinterHealth, etc.
│   │       └── Services/                 # Connection, JobReceiver, PrintExecutor, etc.
│   ├── build/assets/                     # Vite production output
│   ├── css/qrinto-tour.css               # Onboarding tour styles
│   ├── js/qrinto-tour.js                 # Onboarding tour logic
│   ├── fonts/                            # 30 TTF files for canvas text
│   ├── logo/                             # Qrinto branding (PNG)
│   ├── vendor/js/                        # Alpine.js, Lucide, Tailwind fallbacks
│   ├── manifest.json                     # PWA manifest
│   ├── sw.js                             # Service worker
│   └── Qrinto_Terms_and_Privacy_Notice.pdf
├── resources/
│   ├── css/app.css                       # Tailwind directives
│   ├── js/
│   │   ├── app.js                        # Alpine.js + Lucide init
│   │   ├── bootstrap.js                  # Axios CSRF setup
│   │   └── noritsu.jsx                   # React 19 Noritsu editor (Fabric.js)
│   └── views/                            # 132+ Blade templates
│       ├── admin/                        # Admin panel views (30 files)
│       ├── auth/                         # Login/register views
│       ├── checkout/                     # Checkout views
│       ├── components/                   # Reusable Blade components
│       ├── customer/                     # Customer account views
│       ├── emails/                       # Email templates (6 files)
│       ├── layouts/                      # Layout files (9)
│       ├── noritsu/                      # Noritsu editor views
│       ├── products/                     # Public product views
│       ├── profile/                      # Profile views
│       ├── quick-flow/                   # Mobile flow views (20 files)
│       ├── quick-flow-pc/                # Desktop flow views (17 files)
│       └── stores/                       # Store QR view
├── routes/
│   ├── web.php                           # All web routes
│   ├── api.php                           # API routes
│   ├── auth.php                          # Authentication routes (Breeze)
│   └── console.php                       # Artisan commands
├── storage/app/public/
│   ├── custom-prints/                    # Custom print uploads
│   ├── customer-uploads/                 # Customer image uploads
│   ├── designs/                          # Saved design files
│   ├── order-pdfs/                       # Generated order PDFs
│   ├── orders/                           # Order files
│   ├── products/                         # Product images
│   ├── qrintos/                          # QR-related assets
│   ├── stores/                           # Store logos
│   ├── templates/                        # Design templates
│   └── thumbnails/                       # Image thumbnails
├── composer.json                         # PHP dependencies
├── package.json                          # Node dependencies
├── vite.config.js                        # Vite build config (Laravel + React plugins)
├── tailwind.config.js                    # Custom colors, fonts, animations
└── postcss.config.js                     # PostCSS with Tailwind + Autoprefixer
```

---

## 15. Troubleshooting

### Common Issues

| Issue | Cause | Fix |
|-------|-------|-----|
| **Storage images not loading** | Storage symlink not created | Run `php artisan storage:link` |
| **419 Page Expired** | CSRF token mismatch | Ensure `@csrf` in forms; check `resources/js/bootstrap.js` Axios CSRF header |
| **Blank page on `/`** | No active store in session | Visit `/find-store` first or scan a store QR code |
| **PayPal not working** | Missing/wrong credentials | Check `PAYPAL_CLIENT_ID`, `PAYPAL_SECRET`, `PAYPAL_MODE` in `.env` |
| **Vite manifest not found** | Assets not built | Run `npm run build` or `npm run dev` |
| **Print jobs stuck as pending** | QrintoPrintAgent not running | Start the .NET agent on the store's Windows machine |
| **Currency showing wrong** | Store country misconfigured | Check `stores.country` in database - must be "Canada"/"CA"/"CAN" for CAD |
| **Social login redirect error** | OAuth redirect URI mismatch | Ensure `GOOGLE_REDIRECT_URI` matches Google Console config |
| **Queue jobs not processing** | Queue worker not running | Start with `php artisan queue:listen` or `composer dev` |
| **Images not uploading** | PHP upload limits | Check `php.ini`: `upload_max_filesize`, `post_max_size` |
| **Migrations fail** | Duplicate column | Check for duplicate migration `2026_07_28_145151` (adds `special_instructions` twice) |

### Debug Tips

- **Real-time logs:** `php artisan pail` - streams log output in real time
- **Clear all caches:** Visit `{APP_URL}/clear-cache` or run `php artisan optimize:clear`
- **Check routes:** `php artisan route:list`
- **Check model relations:** `php artisan tinker` → `User::find(1)->orders`
- **Mail debugging:** Set `MAIL_MAILER=log` in `.env` to write emails to `storage/logs/`
- **Payment debugging:** PayPal sandbox mode (`PAYPAL_MODE=sandbox`) for testing

---

## 16. Best Practices

### 16.1 Coding Standards

- **PHP:** Follow PSR-12 coding standard. Use Laravel Pint for auto-formatting:
  ```bash
  ./vendor/bin/pint
  ```
- **JavaScript:** Standard ES6+ syntax; Alpine.js for Blade views, React for Noritsu editor
- **CSS:** Tailwind utility classes; avoid custom CSS unless necessary
- **Database:** Always create migrations for schema changes; never modify the database directly in production

### 16.2 Git Workflow

| Branch | Purpose |
|--------|---------|
| `master` | Production branch |
| **Information Required** | Feature branch naming convention not established in codebase |

**Recommended workflow:**
1. Create feature branch from `master`: `git checkout -b feature/your-feature`
2. Make changes and commit with descriptive messages
3. Test locally with `composer dev`
4. Create pull request to `master`
5. Code review → merge

### 16.3 Key Development Conventions

- **Route model binding:** Products use `slug` as route key (`getRouteKeyName()`)
- **Currency:** Always store prices in USD; use `CurrencyService` for display
- **Store scoping:** Always check `auth()->user()->isAdmin()` to determine query scope in admin controllers
- **Guest support:** Cart and orders support nullable `user_id` for guest checkout
- **Image handling:** Use `Intervention\Image` for processing; store in `storage/app/public/`
- **PDF generation:** Use DomPDF via `barryvdh/laravel-dompdf`
- **Slugs:** Use Spatie `HasSlug` trait for auto-slug generation

### 16.4 Security Considerations

- **Never commit `.env`** - contains all secrets and API keys
- **Store printer FTP passwords** are stored as plaintext in the database - consider encrypting
- **API endpoints are unauthenticated** - consider adding Sanctum tokens for production
- **CSRF protection** is active on all web routes
- **Input validation** is handled via Laravel's `$request->validate()` in controllers
- **SQL injection** is prevented by Eloquent ORM and query builder
- **XSS** is prevented by Blade's `{{ }}` auto-escaping (use `{!! !!}` sparingly)

---

## Appendix A: Email Templates

| Template | Path | Type |
|----------|------|------|
| Admin order alert | `resources/views/emails/orders/admin_alert.blade.php` | Markdown |
| Customer confirmation | `resources/views/emails/orders/confirmation.blade.php` | Markdown |
| Status update | `resources/views/emails/orders/status_update.blade.php` | Markdown |
| Store alert | `resources/views/emails/orders/store_alert.blade.php` | Markdown |
| Quick Flow order | `resources/views/emails/quick-flow-order.blade.php` | Blade |
| Store user welcome | `resources/views/emails/store/welcome.blade.php` | Markdown |

## Appendix B: Order Number Formats

| Format | Pattern | Example |
|--------|---------|---------|
| Order Number | `ORD-YYYYMMDD-XXXXX` | `ORD-20260803-48291` |
| Invoice Number | `INV-YYYY-000001` | `INV-2026-000042` |

## Appendix C: Supported Payment Gateways

| Gateway | Status | Configuration |
|---------|--------|---------------|
| PayPal | **Active** | `PAYMENT_GATEWAY=paypal`, sandbox + live modes |
| Stripe | **Available** | `PAYMENT_GATEWAY=stripe`, needs `STRIPE_KEY`/`STRIPE_SECRET` |
| Razorpay | **Available** | `PAYMENT_GATEWAY=razorpay`, needs `RAZORPAY_KEY_ID`/`RAZORPAY_KEY_SECRET` |

---

*This document was generated from codebase analysis on August 3, 2026. Items marked "Information Required" could not be determined from the codebase and should be verified with the project lead.*
