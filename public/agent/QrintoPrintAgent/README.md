# Qrinto Print Agent

Windows service that bridges the Qrinto cloud platform with the Noritsu 931-BL printer at retail store locations.

## Prerequisites

- Windows 10/11 (64-bit)
- .NET 8 SDK
- Noritsu 931-BL PostScript driver installed (`NR05N_PS_E010001_0_0.exe`)

## Project Structure

```
QrintoPrintAgent/
├── Program.cs                      # Entry point, DI setup, Windows Service config
├── QrintoPrintAgent.csproj         # Project file (.NET 8, single-file publish)
├── appsettings.json                # Store ID, API key, printer config, tray defaults
├── src/
│   ├── Config/
│   │   └── AgentConfig.cs          # Strongly-typed config model
│   ├── Core/
│   │   └── PrintAgentWorker.cs     # BackgroundService orchestrator
│   ├── Models/
│   │   ├── PrintJob.cs             # Job model + states (Queued→Completed/Failed)
│   │   ├── TrayMapping.cs          # Media size → tray mapping
│   │   ├── PrinterHealth.cs        # Health snapshot + WebSocket message DTOs
│   │   └── AuditEntry.cs           # Structured audit log entry
│   └── Services/
│       ├── ConnectionManager.cs    # WebSocket + HTTP fallback, auto-reconnect
│       ├── JobReceiver.cs          # Job validation, asset download with retry
│       ├── TrayResolver.cs         # Media→tray mapping, driver tray enumeration
│       ├── PrintExecutor.cs        # PrintDocument, spooler submit, WMI monitoring
│       ├── StatusReporter.cs       # Job result + health reporting to backend
│       └── AuditLogger.cs          # Local JSONL audit logs, 90-day retention
```

## Build & Run

```bash
# Development (console mode)
dotnet run

# Publish as single-file .exe
dotnet publish -c Release -r win-x64 --self-contained true -p:PublishSingleFile=true

# Output: bin/Release/net8.0-windows/win-x64/publish/QrintoPrintAgent.exe
```

## Install as Windows Service

```powershell
sc.exe create QrintoPrintAgent binPath="C:\path\to\QrintoPrintAgent.exe"
sc.exe start QrintoPrintAgent
```

## Configuration

Edit `appsettings.json` before first run:

1. Set `StoreId` and `ApiKey` (provided during Qrinto onboarding)
2. Verify `PrinterName` matches the installed Noritsu driver name
3. Tray mappings are synced automatically from the Admin UI

## Architecture

See `Qrinto_Print_Agent_Architecture.docx` for the full design document.
