# Noritsu MVP Roadmap & Risk Assessment

## 1. MVP Scope
The Minimum Viable Product (MVP) focuses on the "Store Pickup" flow initiated via QR code.

### Core Features
- **QR Entry**: Landing page accepting `store_id`.
- **Photo Upload**: Simple multi-image upload with progress.
- **Product**: Limited to "Folded 5x7 Card" and "Postcard 4x6".
- **Template Engine**: 3-4 Basic Templates (Birthday, Thank You).
- **Editor**:
    - Crop/Pan image within template frame.
    - Edit simple text fields.
- **Fulfillment**: Store Pickup ONLY (Phase 1).
- **Payment**: Stripe Integration (Mobile optimized).
- **Output**: PDF Generation to S3 (triggered on payment).
- **Status**: "New" -> "Printing" -> "delivered_store".

### Excluded from MVP (Deferred to V1)
- Home Delivery.
- Complex text formatting (fonts, colors).
- Advanced photo filters.
- Re-ordering.
- User Accounts (Guest checkout only for MVP).

## 2. Phased Roadmap

### Phase 1: Foundation (Weeks 1-4)
- Setup Database Tables (`stores`, `templates`, `orders`).
- Implement Admin to manage Stores and Templates.
- Create API for Session/Upload.
- Build Frontend Skeleton.

### Phase 2: Editor & Templates (Weeks 5-8)
- Implement `fabric.js` or `react-easy-crop` editor.
- Define JSON structure for templates.
- Create PDF Generation Service (Node.js/Puppeteer or PHP/Snappy).

### Phase 3: Order Flow & Payment (Weeks 9-10)
- Connect Editor to Cart.
- Integrate Stripe.
- Implement Order Status tracking.

### Phase 4: Pilot & Polish (Weeks 11-12)
- Internal testing with sample QR codes.
- Print quality verification.
- Deploy to Pilot Store.

## 3. Risk & Open Issues

### 3.1 Print Quality
- **Risk**: Generated PDFs might have color profile issues (RGB vs CMYK) or resolution drops.
- **Mitigation**: Implement strict 300 DPI checks on upload. Use PDF libraries that support PDF/X-1a if possible.

### 3.2 Image Handling
- **Risk**: Large user uploads (HEIC/48MP) crashing the browser or server.
- **Mitigation**: Client-side resizing (max 4000px) before upload. Server-side async processing.

### 3.3 Store Operations
- **Risk**: Store staff missing the "Delivered" shipment.
- **Mitigation**: Email notifications to Store Manager when a batch is shipped from Noritsu.

### 3.4 Payment & Refunds
- **Risk**: User cancels after printing starts.
- **Policy**: "No cancellations after status 'Printing'". UI must make this clear.

## 4. Next Actions
- [ ] Review Architecture and Screen designs.
- [ ] Approve Database Schema changes (Already applied).
- [ ] Select PDF Generation library.
