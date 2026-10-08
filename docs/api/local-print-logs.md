# Local Print Logs API

REST API for the local-print audit trail. Serves the same data that the web page
`/print/pdf/check` writes via the browser `LPLog` wrapper, and lets the
standalone kiosk / desktop software create and update its own print records.

Every row carries an `origin` field (`web` | `standalone`) so a reader can tell
where a print was triggered from.

- **Base URL**: `{APP_URL}/api/v1/local-print-logs` — e.g. `http://localhost/qrinto/api/v1/local-print-logs`
- **Content-Type**: `application/json`
- **Accept**: `application/json`
- **Auth**: none required right now (public under the `/api/v1` group). Behind a reverse proxy or in production, put it behind the same gateway as the rest of `/api/v1`.

---

## 1. Endpoints at a glance

| # | Method | Path                                | Purpose                                  |
|---|--------|-------------------------------------|------------------------------------------|
| 1 | GET    | `/api/v1/local-print-logs`          | Paginated list with filters              |
| 2 | GET    | `/api/v1/local-print-logs/{id}`     | Single log (includes payload snapshots)  |
| 3 | POST   | `/api/v1/local-print-logs`          | Create row BEFORE sending to the printer |
| 4 | POST   | `/api/v1/local-print-logs/{id}/complete` | Update the SAME row with the outcome |

The two POST endpoints mirror the browser's `LPLog.start()` → `LPLog.finish()`
pair, so the kiosk app should use the same lifecycle:

```
POST /local-print-logs        → { id }           ← create row (status = sending)
→ send job to the printer (PrintTrays, native print, etc.)
POST /local-print-logs/{id}/complete            ← update outcome (completed | failed)
```

---

## 2. The `origin` field (what's new)

| Value         | Meaning                                                         |
|---------------|-----------------------------------------------------------------|
| `web`         | Print triggered from the browser via `LPLog` on the public pages |
| `standalone`  | Print triggered by the kiosk / desktop software via this API    |

Rules:

- On this API, `POST /local-print-logs` **defaults `origin` to `standalone`**. Pass `"origin": "web"` to override.
- The browser `LPLog` wrapper writes `origin = web` through a different internal endpoint.
- Any other value is normalised to `web` server-side (defensive).
- Index can be filtered with `?origin=standalone`.

---

## 3. GET `/api/v1/local-print-logs` — list

Paginated list, newest first.

### Query parameters

All optional. Any combination can be mixed.

| Param              | Type        | Example                 | Notes                                                 |
|--------------------|-------------|-------------------------|-------------------------------------------------------|
| `origin`           | string      | `standalone`            | `web` or `standalone`                                 |
| `status`           | string      | `completed`             | `pending`, `sending`, `sent`, `printing`, `completed`, `failed`, `cancelled` |
| `source`           | string      | `pdf_check`             | Known: `pdf_check`, `own_design_customizer_preview`, `local`, custom kiosk slugs |
| `store_id`         | int         | `3`                     | Exact match                                           |
| `user_id`          | int         | `12`                    | Exact match                                           |
| `printer_name`     | string      | `Noritsu 931BL`         | Exact match                                           |
| `printtrays_job_id`| string      | `pt-abc-123`            | Exact match                                           |
| `search`           | string      | `invoice.pdf`           | LIKE across `file_name`, `printer_name`, `ip_address` |
| `date_start`       | date        | `2026-10-01`            | Inclusive (startOfDay)                                |
| `date_end`         | date        | `2026-10-06`            | Inclusive (endOfDay)                                  |
| `per_page`         | int (1–100) | `25` (default)          | Rows per page                                         |
| `page`             | int         | `1` (default)           | Page number                                           |

### Response

```json
{
  "ok": true,
  "data": [
    {
      "id": 13,
      "origin": "standalone",
      "origin_label": "Standalone",
      "source": "kiosk_app",
      "source_label": "kiosk_app",
      "source_url": "standalone://print",
      "status": "completed",
      "print_type": "pdf",
      "store":   { "id": 3, "name": "Qrinto SG", "code": "SG01" },
      "user":    { "id": 12, "name": "Rohit", "email": "rohit@qrinto.com" },
      "printer": { "name": "Noritsu 931BL (MP Tray)", "id": null, "model": null, "tray": "mp", "tray_label": "MP tray" },
      "media":   { "paper_source": null, "paper_size": null, "media_size": null, "orientation": "portrait", "color_mode": "color", "duplex": "simplex", "scale_mode": null, "scale_percentage": null, "copies": 2 },
      "file":    { "url": null, "name": "sample.pdf", "type": "pdf", "size": 12345, "pages": 1, "dimensions": "4x6" },
      "client":  { "ip": "127.0.0.1", "user_agent": "KioskApp/1.4", "browser": "Unknown", "operating_system": "Windows 10/11", "device_name": "Kiosk-01", "geo": { "city": null, "region": null, "country": null, "country_code": null } },
      "timing":  { "requested_at": "2026-10-06T09:12:33+00:00", "started_at": "2026-10-06T09:12:33+00:00", "completed_at": "2026-10-06T09:12:34+00:00", "failed_at": null, "duration_ms": 842 },
      "linkage": { "order_id": null, "design_id": null, "template_id": null, "product_id": null },
      "printtrays_job_id": "pt-abc-123",
      "error_message": null,
      "created_at": "2026-10-06T09:12:33+00:00",
      "updated_at": "2026-10-06T09:12:34+00:00"
    }
  ],
  "meta": { "current_page": 1, "per_page": 25, "last_page": 1, "total": 1 }
}
```

### cURL

```bash
curl -s "{APP_URL}/api/v1/local-print-logs?origin=standalone&status=completed&per_page=10" \
  -H "Accept: application/json"
```

---

## 4. GET `/api/v1/local-print-logs/{id}` — detail

Returns the same row shape as the list, plus the full `payloads` block (what was
actually sent and received from the printer bridge). Secrets like
`token`/`password`/`apikey` are already scrubbed server-side.

### Response (detail only — the `payloads` block)

```json
{
  "ok": true,
  "data": {
    "...": "same fields as list ...",
    "payloads": {
      "request":          { "...PrintTrays request/config..." },
      "response":         { "...PrintTrays response or error..." },
      "printer_settings": { "...full printer config snapshot..." },
      "design_settings":  { "...design/customizer/size snapshot..." },
      "system":           { "ip": "127.0.0.1", "user_agent": "...", "geo": null, "store": {...}, "user": {...} }
    }
  }
}
```

404 is returned when the id does not exist.

---

## 5. POST `/api/v1/local-print-logs` — create

Call this **before** sending the job to PrintTrays (or whatever print bridge the
kiosk uses). The response gives you the `id` to pass to the complete endpoint.

### Request body

Every field is optional unless noted. All unknown keys are ignored.

| Field              | Type   | Default        | Notes                                                                 |
|--------------------|--------|----------------|-----------------------------------------------------------------------|
| `origin`           | string | `standalone`   | `web` or `standalone` only                                            |
| `source`           | string | `local`        | Free text, e.g. `kiosk_app`, `pdf_check`                              |
| `source_url`       | string | request referer| Where the print was initiated from                                    |
| `print_type`       | string | file type      | `pdf`, `image`                                                        |
| `device_name`      | string | null           | Kiosk hostname / id                                                   |
| `file`             | object | `{}`           | See below                                                             |
| `printer_settings` | object | `{}`           | Printer / media snapshot                                              |
| `design_settings`  | object | `{}`           | Order / design linkage                                                |
| `request_payload`  | object | `{}`           | Full payload about to be sent to the bridge                           |

#### `file` keys

```json
{ "url": "https://…/x.pdf", "name": "x.pdf", "type": "pdf", "size": 12345, "pages": 1, "dimensions": "4x6" }
```

#### `printer_settings` keys (any subset)

```
printer_name, printer_id, printer_model, tray_key, tray_label,
paper_source, paper_size, media_size, orientation, color_mode,
duplex, scale_mode, scale_percentage, copies
```

#### `design_settings` keys (any subset)

```
order_id, design_id, template_id, product_id
```

### Response — 201

```json
{ "ok": true, "id": 42, "data": { "...full row..." } }
```

### Response — 500

```json
{ "ok": false, "error": "log_failed" }
```

Treat this call as **fail-soft** on the client — a logging error must never
block the actual print. If `id` is missing, print anyway and skip the complete
call.

### cURL

```bash
curl -s -X POST "{APP_URL}/api/v1/local-print-logs" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "source": "kiosk_app",
    "device_name": "Kiosk-01",
    "file": {"name":"sample.pdf","type":"pdf","size":12345,"pages":1,"dimensions":"4x6"},
    "printer_settings": {"printer_name":"Noritsu 931BL (MP Tray)","tray_key":"mp","copies":2,"color_mode":"color"},
    "design_settings": {"order_id": null}
  }'
```

---

## 6. POST `/api/v1/local-print-logs/{id}/complete` — update outcome

Call this **after** the print bridge returns (success or failure). Updates the
same row in place; never creates a new one.

### Request body

| Field          | Type    | Required | Notes                                               |
|----------------|---------|----------|-----------------------------------------------------|
| `ok`           | boolean | yes      | `true` → status `completed`; `false` → `failed`     |
| `error`        | string  | no       | Error message when `ok=false`                       |
| `response`     | any     | no       | Full bridge response (object, string, or null)      |
| `job_id`       | string  | no       | PrintTrays / bridge job id, for correlation        |
| `duration_ms`  | integer | no       | Client-measured duration. If omitted, computed server-side from `started_at` |

### Response — 200

```json
{ "ok": true, "data": { "...updated row..." } }
```

404 if the id does not exist; 500 on an unexpected failure
(`{"ok": false, "error": "log_update_failed"}`).

### cURL — success

```bash
curl -s -X POST "{APP_URL}/api/v1/local-print-logs/42/complete" \
  -H "Content-Type: application/json" \
  -d '{"ok":true,"job_id":"pt-abc-123","duration_ms":842,"response":{"message":"queued"}}'
```

### cURL — failure

```bash
curl -s -X POST "{APP_URL}/api/v1/local-print-logs/42/complete" \
  -H "Content-Type: application/json" \
  -d '{"ok":false,"error":"Printer offline","duration_ms":120}'
```

---

## 7. Status lifecycle

```
                 ┌── complete(ok=true) ──► completed
pending → sending│
                 └── complete(ok=false) ─► failed
```

- `pending` — reserved for future async flows; the two POST endpoints above never leave a row in this state.
- `sending` — set by the create endpoint. The row sits here while the print is in flight.
- `completed` / `failed` — terminal, set by the complete endpoint.
- `sent`, `printing`, `cancelled` — reserved for finer-grained flows; filterable but not written by this API.

---

## 8. End-to-end example — JavaScript (standalone kiosk app)

```js
const API = 'http://localhost/qrinto/api/v1/local-print-logs';

async function printWithAudit(file, printerCfg) {
  // 1. Create the log row BEFORE sending to the bridge.
  const t0 = performance.now();
  let logId = null;
  try {
    const r = await fetch(API, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({
        source: 'kiosk_app',
        device_name: 'Kiosk-01',
        file: { name: file.name, type: file.type, size: file.size },
        printer_settings: printerCfg,
      }),
    });
    const j = await r.json();
    logId = (j && j.id) || null;
  } catch (e) {
    // Fail-soft: carry on printing even if logging failed.
    console.warn('[audit] create failed:', e);
  }

  // 2. Do the actual print.
  let ok = true, error = null, response = null, jobId = null;
  try {
    const resp = await bridge.print(printerCfg.printer_name, file.base64, printerCfg);
    response = resp;
    jobId    = resp && resp.job_id;
  } catch (e) {
    ok = false;
    error = e.message || 'Print failed';
    throw e;
  } finally {
    // 3. Record the outcome.
    if (logId) {
      try {
        await fetch(`${API}/${logId}/complete`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            ok,
            error,
            response,
            job_id: jobId,
            duration_ms: Math.round(performance.now() - t0),
          }),
        });
      } catch (e) {
        console.warn('[audit] complete failed:', e);
      }
    }
  }
}
```

## 9. End-to-end example — C# (desktop)

```csharp
using var http = new HttpClient { BaseAddress = new Uri("http://localhost/qrinto/api/v1/") };

// 1. Create the log row.
var createPayload = new {
    source = "kiosk_app",
    device_name = Environment.MachineName,
    file = new { name = "sample.pdf", type = "pdf", size = 12345 },
    printer_settings = new {
        printer_name = "Noritsu 931BL (MP Tray)",
        tray_key     = "mp",
        copies       = 2,
        color_mode   = "color",
    },
};
var createResp = await http.PostAsJsonAsync("local-print-logs", createPayload);
var created    = await createResp.Content.ReadFromJsonAsync<JsonElement>();
long logId     = created.GetProperty("id").GetInt64();

// 2. Print, then complete.
var sw = Stopwatch.StartNew();
bool ok = true; string? error = null;
try {
    bridge.Print(/* … */);
} catch (Exception ex) {
    ok = false; error = ex.Message; throw;
} finally {
    await http.PostAsJsonAsync($"local-print-logs/{logId}/complete", new {
        ok, error, duration_ms = (int) sw.ElapsedMilliseconds
    });
}
```

---

## 10. Where this plugs into the admin

Every row created through this API shows up at `/admin/local-print-logs` with
the **Origin** column set to `Standalone` (violet chip). The admin filter bar
has an **Origin** dropdown so a reviewer can look at web prints only,
standalone prints only, or both.

---

## 11. Change log

| Date       | Change                                                                  |
|------------|-------------------------------------------------------------------------|
| 2026-10-06 | Added `origin` column (`web`/`standalone`) and `/api/v1/local-print-logs` CRUD endpoints. |
| 2026-09-30 | Initial local-print audit trail (`local_print_logs` table, web-only). |

---

## 12. Server-side reference

Code touched by this API, for anyone extending it:

- `app/Http/Controllers/Api/V1/LocalPrintLogApiController.php` — the four endpoints and response formatter.
- `app/Services/LocalPrintLogService.php` — single source of truth for `start()` / `complete()`. Reused by both the web page and the API.
- `app/Models/LocalPrintLog.php` — model + `ORIGIN_WEB` / `ORIGIN_STANDALONE` / `ORIGINS` constants.
- `database/migrations/2026_10_06_120000_add_origin_to_local_print_logs.php` — adds the `origin` column (default `web`, indexed).
- `routes/api.php` — route registration under the `v1` prefix.
- `app/Http/Controllers/Admin/LocalPrintLogController.php` + `resources/views/admin/local-print-logs/index.blade.php` — admin filter + column.
