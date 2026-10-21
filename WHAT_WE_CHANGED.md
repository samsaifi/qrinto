# Qrinto — What We Built & Changed (in simple words)

A plain-English list of all the work done, from the beginning to now. Grouped so it's easy to read. "UI" = what people see; "Behind the scenes" = technical plumbing.

---

## In the very beginning (June 2026)
- Made the card **templates come from the admin panel** instead of being hard-coded. Now staff can add/edit templates without a developer.
- Added a **drag-and-drop template builder** for admins (add text, images, logos onto a canvas).
- Fixed a bug where a saved design got **scrambled/double-saved**.
- Fixed designs where layers were **in the wrong order** (front/back).
- Made the template builder canvas a **neat square**.

## Core shopping app (late June 2026)
- Built the main **product + ordering system**: products, prices, sizes, orders, and the database behind them.
- This is where most of the app's foundation was added at once.

## Printing & designs (July 2026)
- Added a **double-sided (folded) card** print layout.
- Tidied up the **customer flow and page layouts**.
- Added **Events** (like seasonal promotions) and **Paper Types** management in the admin.
- Added an **"AI generate product"** button to help create products faster.

## Desktop shopping experience (August 2026)
- Built a proper **desktop (PC) version** of the shopping flow with a cleaner layout and navigation.
- Rebuilt the **cart and checkout** for desktop.
- Moved the design editor code into its **own file** so it's easier to maintain.
- Applied a **green theme** across the site for a consistent look.

## Big admin + store features (August 2026)
- Expanded the **admin dashboard** and added lots of **admin management screens** (coupons, products, product types, categories, events, templates).
- Added **help/documentation pages** inside the admin.
- Added **Kiosk** support and the **API** (so a separate kiosk/partner app can talk to Qrinto).
- Added **sales & store reports** (per-store revenue, cash vs online, order counts) with **CSV export**.
- Added **local printer management** (set up which trays/paper a store uses).

## Store panel & in-store printing (late Aug – Sept 2026)
- Built a **standalone store panel** where store staff see their orders in columns: **New → Printing → Ready**, plus a **Done** list.
- Added **"Print from the browser"** using a small bridge app (PrintTrays/QZ) that sends the design straight to the store's Noritsu printer — **no manual file saving**.
- Added **QZ Tray setup** that remembers each user so the "Allow printing?" popup only appears once.
- Added a **print-settings popup** to choose tray, paper size, copies, scaling, and orientation before printing.
- Added **print logging** — every print attempt is recorded (who, what printer, success/failure) for troubleshooting.

## New design editor (Sept–Oct 2026)
- Built a **brand-new card editor** (v2) with a modern **Canva-style** layout: a left icon bar and sliding panels.
- Panels added one by one: **Templates, Shapes, Text, Uploads, Background, QR codes, Layers.**
- Editing features: **drag, resize, crop, rotate, undo/redo, zoom, snap guides, and locking** items.
- **Auto-save**: the design saves itself as you work (shows "Saving… / Saved"), so nothing is lost.
- **Works with touch and pen**, not just a mouse.
- **Print guides** (bleed/safe area) so designs aren't cut off.
- **Add a QR code** to a design.
- **Add shapes** (rectangle, circle, triangle, line, heart, star, diamond).
- **Continue → Print**: the editor turns your design into a PDF and sends it to the store print flow.
- Designs now **save to the server** (not just the browser), so you can come back to them.
- Uploaded photos now go to the **server** and are reused by their link instead of being stuffed into the page.

## Local-print polish (Oct 2026)
- Added a **preview page** before printing, with a **swipe slider** to compare.
- Added **file checks** so bad/empty files are caught before printing.
- Improved the **tray configuration** and **PDF preview**.
- Added an **"orders v2"** store view with the print-settings popup built in.

## Printing accuracy fixes (Sept–Oct 2026)
- Fixed how the print command is sent to the bridge so prints go **edge-to-edge** instead of leaving **white borders**.
- Made the app send the printer's **exact paper name** so it uses the right media.
- Baked the orientation into the PDF so prints don't come out **rotated the wrong way**.
- Added a separate **log entry for standalone (kiosk-app) prints** vs website prints, so reports can tell them apart.
- Gave the **kiosk/partner app its own way to read the print logs** through the API.

---

## Honest notes (things to be aware of)
These were found during the review — they're not polished yet:
- **Security:** the partner/kiosk API currently has **no login protection**, and a few admin screens let store staff see or change more than they should. These should be fixed before relying on them publicly.
- **Printing:** the "orientation" toggle doesn't actually change the printed page yet, some designs can be **stretched**, and a failed print can sometimes **print twice**. These need attention and a real printer test.
- **Orders:** very fast double-clicks on "pay with cash" can create a **duplicate order**.
- **Setup:** a fresh install hits a small **database setup snag** (a duplicate step) that needs a one-time fix.

(The full technical detail for all of these is in `QRINTO_DEVELOPER_TECHNICAL_DOCUMENTATION.md`.)
