# Noritsu Screen & Wireframe Guide

## 1. User Journey (Mobile Web)

### 1.1 Start / Landing
- **Trigger**: QR Code Scan (`print.noritsu.com/start?store_id=123`)
- **UI Elements**:
  - Hero image: "Welcome to [Store Name] Printing"
  - Instructions: "Select photos -> Choose design -> Pick up in store"
  - Terms/Consent (optional)
  - CTA Button: "Start Order"

### 1.2 Photo Selection
- **Purpose**: Upload from camera roll.
- **UI Elements**:
  - "Select Photos from Library" (Multiple file support)
  - Grid view of selected images (Thumbnails)
  - Delete button (X) on thumbnails
  - Warning: "Low resolution" on images < 300dpi equivalent.
  - Progress bar for uploads
  - "Next" button (Disabled until uploads complete)

### 1.3 Product/Template Selection
- **Purpose**: Choose format and design.
- **UI Elements**:
  - Tabs: "Folded Cards", "Postcards", "Premium"
  - List of templates (Thumbnails + Names)
  - Categories: "Birthday", "Thank You", "Simple"
  - "Info" button (shows dimensions, paper type)
  - "Select" button

### 1.4 Smart Editor
- **Purpose**: Customize the chosen template.
- **UI Elements**:
  - Canvas area showing the template frame.
  - Image placeholder(s) with draggable/resizable user photo.
  - Controls: Pan, Zoom, Rotate (simple gestures).
  - Text fields (if template allows): "Message", "Name"
  - Safe Area overlay (toggle on/off or subtle guide lines).
  - "Preview" button

### 1.5 Preview & Verify
- **Purpose**: Final check before purchase.
- **UI Elements**:
  - High-quality render of the card (Front/Back/Inside).
  - "Looks Good" button.
  - "Edit" button (returns to editor).

### 1.6 Fulfillment Options
- **Purpose**: Choose delivery method.
- **UI Elements**:
  - Option A: "Pick up at Store" (Pre-selected based on QR code if available).
  - Option B: "Ship to Home" (Form for address entry).
  - Display estimated delivery date/pickup time.

### 1.7 Payment
- **Purpose**: Secure checkout.
- **UI Elements**:
  - Order Summary (Item, Price, Tax, Total).
  - **Contact Info (Guest)**: Email (for receipt), Phone (for status updates).
  - Payment Method: Apple Pay / Google Pay / Credit Card form.
  - "I agree to the Refund Policy" checkbox.
  - "Pay Now" button.

### 1.8 Confirmation & Tracking
- **Purpose**: Success state.
- **UI Elements**:
  - "Order Confirmed!"
  - Order Number: #12345
  - Status Timeline: "Order Received -> Printing -> Ready"
  - "Track Order" link (also sent via Email/SMS).
  - (Optional) "Start New Order" button.

## 2. Admin Dashboard (Desktop Web)

### 2.1 Orders List
- **Table Columns**:
  - Order #
  - Date
  - Customer
  - Products
  - Status (New, Printing, Shipped, Delivered)
  - Store (for pickup)
  - Actions (View, Print PDF)

### 2.2 Order Detail
- **Sections**:
  - Customer Info
  - Order Timeline
  - Product Details (Preview image)
  - Actions: "Download Print PDF", "Mark Shipped", "Refund"

### 2.3 Store Management
- **List**: Active stores, City, Status.
- **Actions**: Edit, Disable, **Download QR Code**.
- **Create/Edit**: Name, Address, Contact Info, Logo Upload.

### 2.4 Template Management
- **List**: Available templates
- **Create/Edit**: Upload background, define slots (JSON editor or UI builder), set pricing.
