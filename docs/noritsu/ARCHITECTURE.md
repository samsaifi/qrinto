# Noritsu Architecture Strategy

## 1. Overview
This document outlines the architecture for the "Noritsu Ordering Web Application".
The goal is to provide a seamless, QR-code-initiated flow that allows users to order print products (photos, cards) directly from their phones, with no app download required. The system handles ordering, PDF generation, and fulfillment (Store Pickup or Home Delivery).

## 2. Core Components

### 2.1 Backend (Laravel)
The existing Laravel application will serve as the core backend, handling:
- **API Endpoints**: RESTful API for the frontend (React/Next.js).
- **Database**: PostgreSQL/MySQL (existing).
- **File Storage**: S3 or local storage for uploaded images and generated PDFs.
- **Background Jobs**: 
    - PDF Generation (using PDF libraries or external services).
    - Image Processing (resizing, optimization).
    - Order Status Updates.
    - Notifications (Email/SMS).

### 2.2 Frontend (React/Next.js)
A mobile-optimized web application (PWA capabilities).
- **Routing**: `react-router` or Next.js file-based routing.
- **State Management**: Context API or Zustand/Redux for complex order state (uploaded images, selected template, edits).
- **Image Editor**: A lightweight canvas-based editor (e.g., `react-easy-crop` or `fabric.js`) for positioning/cropping user photos within template bounds.

### 2.3 Printing Integration (Noritsu)
- **PDF Generation**: A dedicated service or library (e.g., `dompdf`, `snappy`, or a Node.js-based generator like `puppeteer`) to create print-ready PDFs.
- **Constraints**: 
    - All templates must define "Safe Areas" and "Bleed Zones".
    - Output must be high-resolution (300 DPI).
    - Fonts must be embedded.

## 3. Data Model Proposal

### 3.1 Stores (`stores`)
Represents physical locations for pickup.
- `id`: UUID/Integer
- `name`: String
- `address`: String
- `city`, `state`, `zip`: String
- `phone`: String
- `is_active`: Boolean

### 3.2 Templates (`templates`)
Defines the product templates available for users.
- `id`: UUID/Integer
- `name`: String (e.g., "Birthday Card A")
- `category`: Enum (Birthday, Thank You, etc.)
- `structure`: JSON (Defines image slots, text fields, dimensions, safe areas)
- `preview_image_url`: String
- `print_specs`: JSON (DPI, bleed, trim size)

### 3.3 Orders (`orders` - Extends Existing)
- **Status**: Added statuses: `printing`, `ready_for_pickup` (delivered_to_store).
- **Fulfillment Type**: `ship_to_home`, `store_pickup`.
- **Pickup Store ID**: Nullable FK to `stores`.
- **Print Job ID**: Reference to internal print job.

### 3.4 Order Items (`order_items` - Extends Existing)
- **Customization Data**: JSON storing user edits (crop coordinates, text inputs).
- **Generated PDF URL**: Path to the final print-ready file.

## 4. API Structure (Draft)

### 4.1 Client API
- `GET /api/v1/config`: Get app configuration (pricing, constraints).
- `POST /api/v1/session`: Start a new order session (from QR code).
- `POST /api/v1/upload`: Upload user images (returns temporary ID/URL).
- `GET /api/v1/templates`: List available templates.
- `GET /api/v1/stores`: List active stores for pickup.
- `POST /api/v1/orders/preview`: Generate a low-res preview.
- `POST /api/v1/orders`: Create/Finalize order.
- `GET /api/v1/orders/{order_id}`: Track order status.

### 4.2 Admin/Ops API
- `GET /api/v1/admin/orders`: List orders with filtering (status, store).
- `POST /api/v1/admin/orders/{id}/status`: Update status (e.g., to `printing`).
- `GET /api/v1/admin/orders/{id}/pdf`: Download print-ready PDF.

## 5. Implementation Roadmap (MVP)
1. **Database Schema**: Create `stores`, `templates`, update `orders`.
2. **Backend API**: Implement endpoints for session, upload, template listing.
3. **Frontend Shell**: Basic mobile layout with QR code entry handling.
4. **Template System**: Define JSON structure for a simple 5x7 card.
5. **Editor**: Build simple image upload and crop UI.
6. **PDF Generation**: Implement basic PDF creation from order data.
7. **Order Flow**: Connect selection -> edit -> preview -> checkout.
