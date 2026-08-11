<div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-2">
            <i data-lucide="server" class="w-5 h-5 text-brand-600"></i>
            <h2 class="font-display font-semibold text-lg">Printer FTP Configuration</h2>
        </div>
        @if(isset($store) && $store->ftp_host)
        <button type="button" id="testFtpBtn" onclick="testFtpConnection()"
            class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold rounded-xl hover:bg-emerald-100 transition">
            <i data-lucide="wifi" class="w-4 h-4"></i>
            Test Connection
        </button>
        @endif
    </div>

    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-xl">
        <p class="text-xs text-emerald-700 flex items-start gap-2">
            <i data-lucide="info" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
            <span>Configure FTP credentials to send print files directly to the printer's FTP server. The system will upload the generated PDF/print file to the specified remote path on the printer.</span>
        </p>
    </div>

    <div id="ftpTestResult" class="hidden mb-4 p-3 rounded-xl border text-sm font-medium"></div>

    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-surface-700 mb-1">FTP Host / IP <span class="text-red-500">*</span></label>
            <input type="text" name="ftp_host" value="{{ old('ftp_host', $store->ftp_host ?? '') }}"
                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500 font-mono" {!! $readonlyAtts !!}
                placeholder="e.g. 192.168.1.100 or ftp.printer.local">
            @error('ftp_host') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-surface-700 mb-1">FTP Port</label>
            <input type="number" name="ftp_port" value="{{ old('ftp_port', $store->ftp_port ?? 21) }}"
                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500" {!! $readonlyAtts !!}
                placeholder="21" min="1" max="65535">
            @error('ftp_port') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-surface-700 mb-1">FTP Username <span class="text-red-500">*</span></label>
            <input type="text" name="ftp_username" value="{{ old('ftp_username', $store->ftp_username ?? '') }}"
                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500" {!! $readonlyAtts !!}
                placeholder="e.g. printer_user">
            @error('ftp_username') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-surface-700 mb-1">FTP Password <span class="text-red-500">*</span></label>
            <input type="password" name="ftp_password" value="{{ old('ftp_password', $store->ftp_password ?? '') }}"
                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500" {!! $readonlyAtts !!}
                placeholder="{{ isset($store) && $store->ftp_password ? '••••••••' : 'Enter FTP password' }}">
            @error('ftp_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-surface-700 mb-1">Remote Path</label>
            <input type="text" name="ftp_remote_path" value="{{ old('ftp_remote_path', $store->ftp_remote_path ?? '/') }}"
                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500 font-mono" {!! $readonlyAtts !!}
                placeholder="e.g. / or /print_queue/">
            <p class="text-xs text-surface-400 mt-1">Directory on the printer where files are uploaded</p>
            @error('ftp_remote_path') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="flex items-end">
            <label class="flex items-center gap-3 cursor-pointer pb-2">
                <div class="relative">
                    <input type="hidden" name="ftp_passive_mode" value="0">
                    <input type="checkbox" name="ftp_passive_mode" value="1"
                        {{ old('ftp_passive_mode', $store->ftp_passive_mode ?? true) ? 'checked' : '' }}
                        class="sr-only peer" {!! $isStaff ? 'disabled' : '' !!}>
                    <div class="w-11 h-6 bg-surface-300 rounded-full peer peer-checked:bg-brand-600 transition-colors"></div>
                    <div class="absolute left-[2px] top-[2px] w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                </div>
                <span class="text-sm text-surface-700 font-medium">Passive Mode (PASV)</span>
            </label>
        </div>
    </div>

    <!-- Printer Type & Paper Size remain useful for PDF generation -->
    <div class="mt-6 pt-6 border-t border-surface-100">
        <h3 class="text-sm font-semibold text-surface-600 mb-4 flex items-center gap-2">
            <i data-lucide="printer" class="w-4 h-4"></i>
            Print Settings
        </h3>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-surface-700 mb-1">Printer Name / Model</label>
                <input type="text" name="printer_name" value="{{ old('printer_name', $store->printer_name ?? '') }}"
                    class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500" {!! $readonlyAtts !!}
                    placeholder="e.g. Noritsu 931BL">
                @error('printer_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-surface-700 mb-1">Printer Type <span class="text-red-500">*</span></label>
                <select name="printer_type" required
                    class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500" {!! $readonlyAtts !!}>
                    @foreach(['thermal' => 'Thermal', 'inkjet' => 'Inkjet', 'laser' => 'Laser', 'dot_matrix' => 'Dot Matrix'] as $val => $label)
                    <option value="{{ $val }}" {{ old('printer_type', $store->printer_type ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('printer_type') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-surface-700 mb-1">Paper Size <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                    @foreach(['A4' => 'A4', 'A5' => 'A5', 'Letter' => 'Letter', 'Receipt_80mm' => '80mm Receipt', 'Receipt_58mm' => '58mm Receipt'] as $val => $label)
                    <label class="relative">
                        <input type="radio" name="paper_size" value="{{ $val }}"
                            {{ old('paper_size', $store->paper_size ?? 'A4') === $val ? 'checked' : '' }}
                            class="peer sr-only" {!! $isStaff ? 'disabled' : '' !!}>
                        <div class="cursor-pointer text-center px-3 py-2.5 rounded-xl border-2 border-surface-200 text-sm font-medium text-surface-600 transition-all
                            peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-700">
                            {{ $label }}
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('paper_size') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <!-- Legacy IP fields (hidden, for backward compat) -->
    <input type="hidden" name="printer_ip_address" value="{{ old('printer_ip_address', $store->printer_ip_address ?? '') }}">
    <input type="hidden" name="printer_port" value="{{ old('printer_port', $store->printer_port ?? 9100) }}">
</div>
         