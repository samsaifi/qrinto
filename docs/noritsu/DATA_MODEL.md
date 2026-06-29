# Noritsu Data Model

## 1. Relational Schema Proposal

### 1.1 Stores Table (`stores`)
- `id` (PK, Serial)
- `name` (VARCHAR)
- `address` (TEXT)
- `city` (VARCHAR)
- `state` (VARCHAR)
- `zip` (VARCHAR)
- `phone` (VARCHAR)
- `email` (VARCHAR, Nullable)
- `is_active` (BOOLEAN, Default: true)
- `created_at`, `updated_at`

### 1.2 Templates (`templates`)
- `id` (PK, Serial)
- `name` (VARCHAR) - e.g., "5x7 Folded Greeting Card"
- `category` (VARCHAR) - e.g., "Birthday"
- `type` (ENUM) - 'folded_card', 'postcard', 'premium_print'
- `structure` (JSON) - Defines image slots, text fields, safe zones.
- `preview_image_url` (VARCHAR) - S3 URL for web preview.
- `print_specs` (JSON) - DPI, bleed (mm), trim size (mm).
- `price` (DECIMAL) - Base price.
- `created_at`, `updated_at`

### 1.3 Orders (Extension of existing `orders`)
- **Existing Fields**: `id`, `user_id` (make nullable for Guest), `status`, `total`, `payment_status`.
- **New Fields needed**:
    - `guest_email` (VARCHAR, Nullable - for guests)
    - `guest_phone` (VARCHAR, Nullable - for guests)
    - `fulfillment_type` (ENUM: 'pickup', 'shipment')
    - `store_id` (FK to `stores`, Nullable - only for pickup)
    - `shipping_address_id` (FK to addresses, Nullable - only for shipment)
    - `print_job_id` (VARCHAR, Nullable - internal tracking)
    - `estimated_delivery_date` (DATE)

### 1.4 Order Items (Extension of existing `order_items`)
- **Existing Fields**: `id`, `order_id`, `product_id` (FK -> `templates`?), `quantity`.
- **New Fields needed**:
    - `template_id` (FK to `templates`) - If separate from products.
    - `customization_data` (JSON) - Stores user edits:
        ```json
        {
            "slots": [
                { "id": 1, "image_url": "...", "crop": { "x": 10, "y": 10, "w": 100, "h": 100 }, "rotation": 0 }
            ],
            "text": [
                { "id": "msg", "content": "Happy Birthday!" }
            ]
        }
        ```
    - `print_ready_url` (VARCHAR) - S3 path to generated PDF.

### 1.5 Order Status Flow
1. **New** (`pending` payment)
2. **Paid** (`confirmed`)
3. **Printing** (`processing`) - PDF generation started/queued.
4. **Shipped** (`shipped`) - Handed to courier or specialized logic for store shipment.
5. **Delivered to Store** (`delivered_store`) - Arrived at pickup location.
6. **delivered** (`delivered`) - Customer received (pickup or home delivery).
7. **Cancelled** (`cancelled`)
8. **Refunded** (`refunded`)

## 2. API Data Objects (JSON)

### 2.1 Order Request
```json
{
  "store_id": 123,
  "items": [
    {
      "template_id": 45,
      "quantity": 10,
      "customization": { ... }
    }
  ],
  "fulfillment": "pickup",
  "payment_token": "tok_visa"
}
```

### 2.2 Order Response
```json
{
  "id": 999,
  "status": "confirmed",
  "estimated_delivery": "2023-10-25",
  "tracking_url": "..."
}
```
