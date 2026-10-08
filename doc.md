# Qrinto — Local Setup Guide (Move to Another PC)

This guide explains how to take the Qrinto project (Laravel + Vite) from one PC and run it locally on another PC using **XAMPP**.

---

## What you receive

1. **`qrinto_project.zip`** — the full project source code.
2. **`p2_database.sql`** — the full database dump (schema + data).
3. **`doc.md`** — this document.

---

## Prerequisites (install on the new PC)

| Tool | Version | Notes |
|------|---------|-------|
| **XAMPP** | PHP **8.2+** | Gives you Apache + MySQL/MariaDB. |
| **Composer** | latest | PHP dependency manager — https://getcomposer.org |
| **Node.js** | 18+ (LTS) | Includes `npm`, needed for Vite front-end build. |

Make sure `php`, `composer`, and `node`/`npm` are available in your terminal (PATH).

---

## Step 1 — Unzip the project

Extract `qrinto_project.zip` into your XAMPP web root so the folder becomes:

```
C:\xampp\htdocs\qrinto
```

> The app URL assumes the folder is named `qrinto` under `htdocs`. If you use a different folder name, update `APP_URL` in `.env` (Step 4) accordingly.

---

## Step 2 — Start XAMPP services

Open the **XAMPP Control Panel** and start:

- **Apache**
- **MySQL**

---

## Step 3 — Import the database

1. Open **phpMyAdmin**: http://localhost/phpmyadmin
2. Create a new database named **`p2`** (collation `utf8mb4_unicode_ci`).
3. Select the `p2` database → **Import** tab → choose **`p2_database.sql`** → **Go**.

**Or via command line:**

```bash
C:\xampp\mysql\bin\mysql.exe -u root p2 < p2_database.sql
```

(First create the DB if needed: `CREATE DATABASE p2;`)

---

## Step 4 — Configure the environment (`.env`)

If `.env` is missing, copy the example:

```bash
copy .env.example .env
```

Then confirm these values in `.env` match the new PC:

```env
APP_NAME="Qrinto"
APP_URL=http://localhost/qrinto/

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=p2
DB_USERNAME=root
DB_PASSWORD=
```

> Default XAMPP MySQL user is `root` with an **empty** password. Adjust if your setup differs.

---

## Step 5 — Install dependencies

From the project root (`C:\xampp\htdocs\qrinto`):

```bash
composer install
```

```bash
npm install
```

---

## Step 6 — App key & clear cache

Generate the app key (only if it's empty in `.env`):

```bash
php artisan key:generate
```

Clear and rebuild all caches:

```bash
php artisan optimize:clear
```

That runs config/route/view/cache clears together. (Individually: `php artisan config:clear`, `route:clear`, `view:clear`, `cache:clear`.)

Fix storage symlink (for uploaded/public files):

```bash
php artisan storage:link
```

---

## Step 7 — Build & run the front-end (Vite)

**For development** (hot reload — keep this terminal running):

```bash
npm run dev
```

**For a production build** (compiled assets, no dev server needed):

```bash
npm run build
```

---

## Step 8 — Open the app

With Apache running and (for dev) `npm run dev` active, open:

```
http://localhost/qrinto/
```

---

## Quick reference — full sequence

```bash
# 1. unzip into C:\xampp\htdocs\qrinto, start Apache + MySQL in XAMPP

# 2. import DB
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS p2"
C:\xampp\mysql\bin\mysql.exe -u root p2 < p2_database.sql

# 3. from project root
composer install
npm install
copy .env.example .env   # only if .env missing
php artisan key:generate # only if APP_KEY empty
php artisan optimize:clear
php artisan storage:link

# 4. run
npm run dev              # development (keep running)
# or
npm run build            # production assets
```

Then browse to **http://localhost/qrinto/**

---

## Troubleshooting

- **500 / blank page** → run `php artisan optimize:clear`, check `storage/logs/laravel.log`, ensure `storage/` and `bootstrap/cache/` are writable.
- **DB connection error** → verify MySQL is running and `.env` DB credentials match (`p2`, `root`, empty password).
- **Assets not loading / old CSS** → re-run `npm run build` (or start `npm run dev`) and hard-refresh the browser.
- **`vite manifest not found`** → you must run `npm run dev` or `npm run build` at least once.
- **Wrong URLs / redirects** → make sure `APP_URL` in `.env` matches your folder name and host.
