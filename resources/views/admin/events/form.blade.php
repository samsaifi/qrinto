@extends('layouts.admin')
@section('title', isset($event) ? 'Edit Event' : 'Create Event')

@section('content')
    <div class="max-w-2xl">
        <h1 class="font-display font-bold text-2xl text-surface-900 mb-8">{{ isset($event) ? 'Edit Event' : 'Create Event' }}
        </h1>

        <form action="{{ isset($event) ? route('admin.events.update', $event) : route('admin.events.store') }}" method="POST"
            class="space-y-6">
            @csrf
            @if (isset($event))
                @method('PUT')
            @endif

            <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6 space-y-5">
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1">Event Title *</label>
                    <input type="text" name="title" value="{{ old('title', $event->title ?? '') }}" required
                        class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                    @error('title')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Start Date</label>
                        <input type="date" name="start_date"
                            value="{{ old('start_date', isset($event) && $event->start_date ? $event->start_date->format('Y-m-d') : '') }}"
                            class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                        @error('start_date')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">End Date</label>
                        <input type="date" name="end_date"
                            value="{{ old('end_date', isset($event) && $event->end_date ? $event->end_date->format('Y-m-d') : '') }}"
                            class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                        @error('end_date')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Icon --}}
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1">Event Icon</label>
                    <div class="flex gap-3 mb-2">
                        <label class="flex items-center gap-2 cursor-pointer text-sm">
                            <input type="radio" name="icon_type" value="lucide" id="icon_lucide_radio"
                                {{ old('icon_type', $event->icon_type ?? 'lucide') === 'lucide' ? 'checked' : '' }}
                                onchange="toggleIconType()">
                            Lucide icon name
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm">
                            <input type="radio" name="icon_type" value="svg" id="icon_svg_radio"
                                {{ old('icon_type', $event->icon_type ?? '') === 'svg' ? 'checked' : '' }}
                                onchange="toggleIconType()">
                            Paste SVG
                        </label>
                    </div>
                    <div id="icon-lucide-row">
                        <div class="relative flex gap-2 items-center">
                            <div class="relative flex-1">
                                <input id="icon_lucide" name="icon_lucide" type="text" autocomplete="off"
                                    value="{{ old('icon_lucide', isset($event) && ($event->icon_type ?? 'lucide') === 'lucide' ? $event->icon_svg : '') }}"
                                    placeholder="e.g. cake, heart, sparkles"
                                    class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                                <div id="icon-suggestions"
                                    class="hidden absolute z-50 left-0 right-0 top-full mt-1 bg-white border border-surface-200 rounded-xl shadow-lg max-h-56 overflow-y-auto">
                                </div>
                            </div>
                            <div id="icon-preview"
                                class="w-10 h-10 rounded-lg bg-surface-100 flex items-center justify-center flex-shrink-0">
                                <i id="icon-preview-el" data-lucide="calendar" class="w-5 h-5 text-surface-500"></i>
                            </div>
                        </div>
                    </div>
                    <div id="icon-svg-row" class="hidden">
                        <textarea name="icon_svg_raw" rows="4" placeholder="Paste SVG markup here..."
                            class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500 text-sm font-mono">{{ old('icon_svg_raw', isset($event) && ($event->icon_type ?? '') === 'svg' ? $event->icon_svg : '') }}</textarea>
                    </div>
                    <input type="hidden" name="icon_svg" id="icon_svg_hidden"
                        value="{{ old('icon_svg', $event->icon_svg ?? '') }}">
                    @error('icon_svg')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1">Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="color" value="{{ old('color', $event->color ?? '#000000') }}"
                            class="h-10 w-14 rounded-lg border-surface-200 cursor-pointer">
                        <span class="text-sm text-surface-500">{{ old('color', $event->color ?? '#000000') }}</span>
                    </div>
                    @error('color')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-surface-700">
                    <input type="checkbox" name="is_active" value="1"
                        {{ old('is_active', $event->is_active ?? true) ? 'checked' : '' }}
                        class="rounded text-brand-600 focus:ring-brand-500"> Active
                </label>

                {{-- Stores multi-select (admin) or read-only badge (store admin) --}}
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1">Stores</label>
                    @if (auth()->user()->isAdmin())
                        @error('stores')
                            <p class="text-xs text-red-500 mb-1">{{ $message }}</p>
                        @enderror
                        @include('admin.events._multiselect', [
                            'items' => $stores->map(fn($s) => ['id' => $s->id, 'name' => $s->store_name]),
                            'selectedIds' => old(
                                'stores',
                                isset($event)
                                    ? $event->stores->pluck('id')->map(fn($id) => (string) $id)->toArray()
                                    : []),
                            'fieldName' => 'stores',
                            'placeholder' => 'Select stores...',
                            'tagColor' => 'emerald',
                        ])
                    @else
                        <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-md  bg-gray-50  text-gray-700">
                            {{ auth()->user()->store->store_name ?? 'Your Store' }}
                        </span>
                        <p class="text-xs text-surface-400 mt-1">Event will be assigned to your store automatically.</p>
                    @endif
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                    class="px-6 py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
                    {{ isset($event) ? 'Update' : 'Create' }} Event
                </button>
                <a href="{{ route('admin.events.index') }}"
                    class="px-6 py-3 bg-surface-100 text-surface-600 font-semibold rounded-xl hover:bg-surface-200 transition">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleIconType() {
            const isLucide = document.getElementById('icon_lucide_radio').checked;
            document.getElementById('icon-lucide-row').classList.toggle('hidden', !isLucide);
            document.getElementById('icon-svg-row').classList.toggle('hidden', isLucide);
        }

        function refreshIconPreview() {
            const name = document.getElementById('icon_lucide').value.trim() || 'calendar';
            const el = document.getElementById('icon-preview-el');
            el.setAttribute('data-lucide', name);
            if (window.lucide) lucide.createIcons({
                nodes: [el]
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            toggleIconType();

            const input = document.getElementById('icon_lucide');
            const dropdown = document.getElementById('icon-suggestions');
            let activeIdx = -1;

            // Comprehensive Lucide icon names list
            const allNames = [
                'accessibility', 'activity', 'air-vent', 'airplay', 'alarm-check', 'alarm-clock',
                'alarm-clock-off', 'alarm-minus', 'alarm-plus',
                'album', 'alert-circle', 'alert-octagon', 'alert-triangle', 'align-center',
                'align-center-horizontal', 'align-center-vertical',
                'align-end-horizontal', 'align-end-vertical', 'align-horizontal-distribute-center',
                'align-horizontal-distribute-end',
                'align-horizontal-distribute-start', 'align-horizontal-justify-center',
                'align-horizontal-justify-end',
                'align-horizontal-justify-start', 'align-horizontal-space-around',
                'align-horizontal-space-between',
                'align-justify', 'align-left', 'align-right', 'align-start-horizontal', 'align-start-vertical',
                'align-vertical-distribute-center', 'align-vertical-distribute-end',
                'align-vertical-distribute-start',
                'align-vertical-justify-center', 'align-vertical-justify-end', 'align-vertical-justify-start',
                'align-vertical-space-around', 'align-vertical-space-between', 'ampersand', 'ampersands',
                'anchor',
                'angry', 'annoyed', 'antenna', 'aperture', 'app-window', 'apple', 'archive', 'archive-restore',
                'archive-x',
                'area-chart', 'armchair', 'arrow-big-down', 'arrow-big-left', 'arrow-big-right', 'arrow-big-up',
                'arrow-down', 'arrow-down-circle', 'arrow-down-left', 'arrow-down-right', 'arrow-down-to-dot',
                'arrow-down-to-line', 'arrow-left', 'arrow-left-circle', 'arrow-left-right',
                'arrow-left-to-line',
                'arrow-right', 'arrow-right-circle', 'arrow-right-to-line', 'arrow-up', 'arrow-up-circle',
                'arrow-up-down', 'arrow-up-left', 'arrow-up-right', 'arrow-up-to-line', 'arrows-up-from-line',
                'asterisk', 'at-sign', 'atom', 'award', 'axe', 'axis-3d',
                'baby', 'backpack', 'badge', 'badge-alert', 'badge-check', 'badge-dollar-sign', 'badge-euro',
                'badge-help',
                'badge-indian-rupee', 'badge-info', 'badge-japanese-yen', 'badge-minus', 'badge-percent',
                'badge-plus',
                'badge-pound-sterling', 'badge-russian-ruble', 'badge-swiss-franc', 'badge-x', 'banknote',
                'bar-chart',
                'bar-chart-2', 'bar-chart-3', 'bar-chart-4', 'bar-chart-big', 'bar-chart-horizontal',
                'bar-chart-horizontal-big', 'baseline', 'bath', 'battery', 'battery-charging', 'battery-full',
                'battery-low', 'battery-medium', 'battery-warning', 'beaker', 'bean', 'bean-off', 'bed',
                'bed-double',
                'bed-single', 'beef', 'beer', 'bell', 'bell-dot', 'bell-minus', 'bell-off', 'bell-plus',
                'bell-ring',
                'bike', 'binary', 'biohazard', 'bird', 'bitcoin', 'blend', 'blinds', 'blocks', 'bluetooth',
                'bluetooth-connected', 'bluetooth-off', 'bluetooth-searching', 'bold', 'bolt', 'bomb', 'bone',
                'book',
                'book-copy', 'book-down', 'book-key', 'book-lock', 'book-marked', 'book-minus', 'book-open',
                'book-open-check', 'book-open-text', 'book-plus', 'book-template', 'book-text', 'book-type',
                'book-up',
                'book-user', 'book-x', 'bookmark', 'bookmark-minus', 'bookmark-plus', 'boom-box', 'bot', 'box',
                'box-select', 'boxes', 'braces', 'brackets', 'brain', 'brain-circuit', 'brain-cog',
                'brick-wall',
                'briefcase', 'bring-to-front', 'brush', 'bug', 'building', 'building-2', 'bus', 'bus-front',
                'cable', 'cable-car', 'cake', 'cake-slice', 'calculator', 'calendar', 'calendar-check',
                'calendar-check-2',
                'calendar-clock', 'calendar-days', 'calendar-heart', 'calendar-minus', 'calendar-off',
                'calendar-plus',
                'calendar-range', 'calendar-search', 'calendar-x', 'camera', 'camera-off', 'candlestick-chart',
                'candy', 'candy-cane', 'candy-off', 'car', 'car-front', 'car-taxi-front', 'caravan', 'carrot',
                'case-lower', 'case-sensitive', 'case-upper', 'cast', 'castle', 'cat', 'check', 'check-check',
                'check-circle', 'check-circle-2', 'check-square', 'check-square-2', 'chef-hat', 'cherry',
                'chevron-down', 'chevron-first', 'chevron-last', 'chevron-left', 'chevron-right', 'chevron-up',
                'chevrons-down', 'chevrons-down-up', 'chevrons-left', 'chevrons-left-right', 'chevrons-right',
                'chevrons-right-left', 'chevrons-up', 'chevrons-up-down', 'chrome', 'church', 'cigarette',
                'cigarette-off', 'circle', 'circle-dashed', 'circle-dot', 'circle-ellipsis', 'circle-equal',
                'circle-off', 'circle-slash', 'circle-slash-2', 'circuit-board', 'citrus', 'clapperboard',
                'clipboard', 'clipboard-check', 'clipboard-copy', 'clipboard-edit', 'clipboard-list',
                'clipboard-paste', 'clipboard-signature', 'clipboard-type', 'clipboard-x', 'clock', 'clock-1',
                'clock-10', 'clock-11', 'clock-12', 'clock-2', 'clock-3', 'clock-4', 'clock-5', 'clock-6',
                'clock-7',
                'clock-8', 'clock-9', 'cloud', 'cloud-cog', 'cloud-drizzle', 'cloud-fog', 'cloud-hail',
                'cloud-lightning',
                'cloud-moon', 'cloud-moon-rain', 'cloud-off', 'cloud-rain', 'cloud-rain-wind', 'cloud-snow',
                'cloud-sun', 'cloud-sun-rain', 'clover', 'club', 'code', 'code-2', 'codepen', 'codesandbox',
                'coffee',
                'cog', 'coins', 'columns', 'combine', 'command', 'compass', 'component', 'computer',
                'concierge-bell',
                'cone', 'construction', 'contact', 'contact-2', 'container', 'contrast', 'cookie', 'copy',
                'copy-check',
                'copy-minus', 'copy-plus', 'copy-slash', 'copy-x', 'copyleft', 'corner-down-left',
                'corner-down-right',
                'corner-left-down', 'corner-left-up', 'corner-right-down', 'corner-right-up', 'corner-up-left',
                'corner-up-right', 'cpu', 'creative-commons', 'credit-card', 'croissant', 'crop', 'cross',
                'crosshair',
                'crown', 'cup-soda', 'currency', 'database', 'database-backup', 'database-zap', 'delete',
                'dessert', 'diamond', 'dice-1', 'dice-2', 'dice-3', 'dice-4', 'dice-5', 'dice-6', 'dices',
                'diff',
                'disc', 'disc-2', 'disc-3', 'divide', 'divide-circle', 'divide-square', 'dna', 'dna-off', 'dog',
                'dollar-sign', 'donut', 'door-closed', 'door-open', 'dot', 'download', 'download-cloud',
                'drafting-compass', 'drama', 'dribbble', 'droplet', 'droplets', 'drum', 'drumstick', 'dumbbell',
                'ear', 'ear-off', 'egg', 'egg-fried', 'egg-off', 'equal', 'equal-not', 'eraser', 'euro',
                'expand', 'external-link', 'eye', 'eye-off',
                'facebook', 'factory', 'fan', 'fast-forward', 'feather', 'ferris-wheel', 'figma', 'file',
                'file-archive',
                'file-audio', 'file-audio-2', 'file-axis-3d', 'file-badge', 'file-badge-2', 'file-bar-chart',
                'file-bar-chart-2', 'file-box', 'file-check', 'file-check-2', 'file-clock', 'file-code',
                'file-code-2', 'file-cog', 'file-diff', 'file-digit', 'file-down', 'file-edit', 'file-heart',
                'file-image', 'file-input', 'file-json', 'file-json-2', 'file-key', 'file-key-2',
                'file-line-chart',
                'file-lock', 'file-lock-2', 'file-minus', 'file-minus-2', 'file-output', 'file-pie-chart',
                'file-plus', 'file-plus-2', 'file-question', 'file-scan', 'file-search', 'file-search-2',
                'file-signature', 'file-spreadsheet', 'file-stack', 'file-symlink', 'file-terminal',
                'file-text',
                'file-type', 'file-type-2', 'file-up', 'file-video', 'file-video-2', 'file-volume',
                'file-volume-2', 'file-warning', 'file-x', 'file-x-2', 'files', 'film', 'filter', 'filter-x',
                'fingerprint', 'fire-extinguisher', 'fish', 'fish-off', 'fish-symbol', 'flag', 'flag-off',
                'flag-triangle-left', 'flag-triangle-right', 'flame', 'flashlight', 'flashlight-off',
                'flask-conical', 'flask-conical-off', 'flask-round', 'flip-horizontal', 'flip-horizontal-2',
                'flip-vertical', 'flip-vertical-2', 'flower', 'flower-2', 'focus', 'fold-horizontal',
                'fold-vertical',
                'folder', 'folder-archive', 'folder-check', 'folder-clock', 'folder-closed', 'folder-cog',
                'folder-dot', 'folder-down', 'folder-edit', 'folder-git', 'folder-git-2', 'folder-heart',
                'folder-input', 'folder-kanban', 'folder-key', 'folder-lock', 'folder-minus', 'folder-open',
                'folder-open-dot', 'folder-output', 'folder-plus', 'folder-root', 'folder-search',
                'folder-search-2',
                'folder-symlink', 'folder-sync', 'folder-tree', 'folder-up', 'folder-x', 'folders',
                'footprints',
                'forklift', 'form-input', 'forward', 'frame', 'framer', 'frown', 'fuel', 'fullscreen',
                'gallery-horizontal', 'gallery-horizontal-end', 'gallery-thumbnails', 'gallery-vertical',
                'gallery-vertical-end', 'gamepad', 'gamepad-2', 'gauge', 'gauge-circle', 'gavel', 'gem',
                'ghost',
                'gift', 'git-branch', 'git-branch-plus', 'git-commit', 'git-compare', 'git-fork',
                'git-merge', 'git-pull-request', 'git-pull-request-closed', 'git-pull-request-draft', 'github',
                'gitlab', 'glass-water', 'glasses', 'globe', 'globe-2', 'goal', 'grab', 'graduation-cap',
                'grape',
                'grid-2x2', 'grid-3x3', 'grip', 'grip-horizontal', 'grip-vertical', 'group', 'guitar',
                'hammer', 'hand', 'hand-metal', 'hard-drive', 'hard-drive-download', 'hard-drive-upload',
                'hash',
                'haze', 'hdmi-port', 'heading', 'heading-1', 'heading-2', 'heading-3', 'heading-4', 'heading-5',
                'heading-6', 'headphones', 'heart', 'heart-crack', 'heart-handshake', 'heart-off',
                'heart-pulse',
                'heater', 'help-circle', 'helpline', 'hexagon', 'highlighter', 'history', 'home', 'hop',
                'hop-off',
                'hotel', 'hourglass', 'ice-cream', 'ice-cream-2', 'image', 'image-minus', 'image-off',
                'image-plus',
                'import', 'inbox', 'indent', 'indian-rupee', 'infinity', 'info', 'inspection-panel',
                'instagram',
                'italic', 'iteration-ccw', 'iteration-cw',
                'japanese-yen', 'joystick',
                'kanban', 'key', 'key-round', 'key-square', 'keyboard', 'lamp', 'lamp-ceiling', 'lamp-desk',
                'lamp-floor', 'lamp-wall-down', 'lamp-wall-up', 'landmark', 'languages', 'laptop', 'laptop-2',
                'lasso', 'lasso-select', 'laugh', 'layers', 'layers-2', 'layers-3', 'layout',
                'layout-dashboard',
                'layout-grid', 'layout-list', 'layout-panel-left', 'layout-panel-top', 'layout-template',
                'leaf',
                'leafy-green', 'library', 'library-big', 'life-buoy', 'ligature', 'lightbulb', 'lightbulb-off',
                'line-chart', 'link', 'link-2', 'link-2-off', 'linkedin', 'list', 'list-checks',
                'list-collapse',
                'list-end', 'list-filter', 'list-minus', 'list-music', 'list-ordered', 'list-plus',
                'list-restart',
                'list-start', 'list-todo', 'list-tree', 'list-video', 'list-x', 'loader', 'loader-2', 'locate',
                'locate-fixed', 'locate-off', 'lock', 'lock-keyhole', 'log-in', 'log-out', 'lollipop',
                'luggage', 'magnet', 'mail', 'mail-check', 'mail-minus', 'mail-open', 'mail-plus',
                'mail-question',
                'mail-search', 'mail-warning', 'mail-x', 'mailbox', 'mails', 'map', 'map-pin', 'map-pin-off',
                'map-pinned', 'martini', 'maximize', 'maximize-2', 'medal', 'megaphone', 'meh', 'memory-stick',
                'menu', 'merge', 'message-circle', 'message-circle-code', 'message-circle-dashed',
                'message-circle-heart', 'message-circle-more', 'message-circle-off', 'message-circle-plus',
                'message-circle-question', 'message-circle-reply', 'message-circle-warning', 'message-circle-x',
                'message-square', 'message-square-code', 'message-square-dashed', 'message-square-diff',
                'message-square-dot', 'message-square-heart', 'message-square-more', 'message-square-off',
                'message-square-plus', 'message-square-quote', 'message-square-reply', 'message-square-share',
                'message-square-text', 'message-square-warning', 'message-square-x', 'messages-square',
                'mic', 'mic-2', 'mic-off', 'microscope', 'microwave', 'milestone', 'milk', 'milk-off',
                'minimize',
                'minimize-2', 'minus', 'minus-circle', 'minus-square', 'monitor', 'monitor-check',
                'monitor-dot', 'monitor-down', 'monitor-off', 'monitor-pause', 'monitor-play',
                'monitor-smartphone',
                'monitor-speaker', 'monitor-stop', 'monitor-up', 'monitor-x', 'moon', 'moon-star',
                'more-horizontal',
                'more-vertical', 'mountain', 'mountain-snow', 'mouse', 'mouse-pointer', 'mouse-pointer-2',
                'mouse-pointer-click', 'move', 'move-3d', 'move-diagonal', 'move-diagonal-2', 'move-down',
                'move-down-left', 'move-down-right', 'move-horizontal', 'move-left', 'move-right', 'move-up',
                'move-up-left', 'move-up-right', 'move-vertical', 'music', 'music-2', 'music-3', 'music-4',
                'navigation', 'navigation-2', 'navigation-2-off', 'navigation-off', 'network', 'newspaper',
                'nfc', 'notebook', 'notebook-pen', 'notebook-tabs', 'notepad-text', 'notepad-text-dashed',
                'nut', 'nut-off',
                'octagon', 'option', 'orbit', 'outdent',
                'package', 'package-2', 'package-check', 'package-minus', 'package-open', 'package-plus',
                'package-search', 'package-x', 'paint-bucket', 'paintbrush', 'paintbrush-2', 'palette',
                'palmtree', 'pan-bottom-left', 'pan-bottom-right', 'pan-top-left', 'pan-top-right',
                'panel-bottom', 'panel-bottom-close', 'panel-bottom-dashed', 'panel-bottom-open', 'panel-left',
                'panel-left-close', 'panel-left-dashed', 'panel-left-open', 'panel-right', 'panel-right-close',
                'panel-right-dashed', 'panel-right-open', 'panel-top', 'panel-top-close', 'panel-top-dashed',
                'panel-top-open', 'paperclip', 'parentheses', 'parking-circle', 'parking-circle-off',
                'parking-meter', 'parking-square', 'parking-square-off', 'party-popper', 'pause',
                'pause-circle',
                'pause-octagon', 'paw-print', 'pc-case', 'pen', 'pen-line', 'pen-square', 'pen-tool', 'pencil',
                'pencil-line', 'pencil-ruler', 'pentagon', 'percent', 'person-standing', 'phone', 'phone-call',
                'phone-forwarded', 'phone-incoming', 'phone-missed', 'phone-off', 'phone-outgoing', 'pi',
                'piano', 'picture-in-picture', 'picture-in-picture-2', 'pie-chart', 'piggy-bank', 'pilcrow',
                'pilcrow-square', 'pill', 'pin', 'pin-off', 'pipette', 'pizza', 'plane', 'plane-landing',
                'plane-takeoff', 'play', 'play-circle', 'play-square', 'plug', 'plug-2', 'plug-zap',
                'plug-zap-2',
                'plus', 'plus-circle', 'plus-square', 'pocket', 'pocket-knife', 'podcast', 'pointer', 'popcorn',
                'popsicle', 'pound-sterling', 'power', 'power-circle', 'power-off', 'power-square',
                'presentation',
                'printer', 'projector', 'proportions', 'puzzle',
                'qr-code', 'quote',
                'rabbit', 'radar', 'radiation', 'radio', 'radio-receiver', 'radio-tower', 'rainbow', 'rat',
                'ratio', 'receipt', 'receipt-text', 'rectangle-horizontal', 'rectangle-vertical', 'recycle',
                'redo', 'redo-2', 'redo-dot', 'refresh-ccw', 'refresh-ccw-dot', 'refresh-cw', 'refresh-cw-off',
                'refrigerator', 'regex', 'remove-formatting', 'repeat', 'repeat-1', 'repeat-2', 'replace',
                'replace-all', 'reply', 'reply-all', 'rewind', 'ribbon', 'rocket', 'rocking-chair',
                'roller-coaster',
                'rotate-3d', 'rotate-ccw', 'rotate-cw', 'route', 'route-off', 'router', 'rows', 'rows-2',
                'rows-3',
                'rows-4', 'rss', 'ruler', 'russian-ruble',
                'sailboat', 'salad', 'sandwich', 'satellite', 'satellite-dish', 'save', 'save-all', 'scale',
                'scale-3d', 'scaling', 'scan', 'scan-barcode', 'scan-eye', 'scan-face', 'scan-line',
                'scan-search',
                'scan-text', 'scatter-chart', 'school', 'school-2', 'scissors', 'scissors-line-dashed',
                'screen-share', 'screen-share-off', 'scroll', 'scroll-text', 'search', 'search-check',
                'search-code', 'search-slash', 'search-x', 'send', 'send-horizontal', 'send-to-back',
                'separator-horizontal', 'separator-vertical', 'server', 'server-cog', 'server-crash',
                'server-off',
                'settings', 'settings-2', 'shapes', 'share', 'share-2', 'sheet', 'shell', 'shield',
                'shield-alert',
                'shield-ban', 'shield-check', 'shield-ellipsis', 'shield-half', 'shield-minus', 'shield-off',
                'shield-plus', 'shield-question', 'shield-x', 'ship', 'ship-wheel', 'shirt', 'shopping-bag',
                'shopping-basket', 'shopping-cart', 'shovel', 'shower-head', 'shrink', 'shrub', 'shuffle',
                'sigma', 'signal', 'signal-high', 'signal-low', 'signal-medium', 'signal-zero', 'siren',
                'skip-back',
                'skip-forward', 'skull', 'slack', 'slash', 'slice', 'sliders', 'sliders-horizontal',
                'smartphone',
                'smartphone-charging', 'smartphone-nfc', 'smile', 'smile-plus', 'snail', 'snowflake', 'sofa',
                'soup', 'space', 'spade', 'sparkle', 'sparkles', 'speaker', 'speech', 'spell-check',
                'spell-check-2',
                'spline', 'split', 'split-square-horizontal', 'split-square-vertical', 'spray-can', 'sprout',
                'square', 'square-asterisk', 'square-code', 'square-dashed-bottom', 'square-dashed-bottom-code',
                'square-dot', 'square-equal', 'square-gantt-chart', 'square-kanban', 'square-library',
                'square-menu', 'square-minus', 'square-mouse-pointer', 'square-parking', 'square-parking-off',
                'square-pen', 'square-percent', 'square-pi', 'square-pilcrow', 'square-play', 'square-plus',
                'square-power', 'square-radical', 'square-scissors', 'square-sigma', 'square-slash',
                'square-split-horizontal', 'square-split-vertical', 'square-stack', 'square-terminal',
                'square-user', 'square-user-round', 'square-x', 'squircle', 'squirrel', 'stamp', 'star',
                'star-half', 'star-off', 'step-back', 'step-forward', 'stethoscope', 'sticker', 'sticky-note',
                'store', 'stretch-horizontal', 'stretch-vertical', 'strikethrough', 'subscript', 'subtitles',
                'sun', 'sun-dim', 'sun-medium', 'sun-moon', 'sun-snow', 'sunrise', 'sunset', 'superscript',
                'swatch-book', 'swiss-franc', 'switch-camera', 'sword', 'swords', 'syringe',
                'table', 'table-2', 'table-properties', 'tablet', 'tablet-smartphone', 'tablets', 'tag', 'tags',
                'tally-1', 'tally-2', 'tally-3', 'tally-4', 'tally-5', 'tangent', 'target', 'tent', 'tent-tree',
                'terminal', 'terminal-square', 'test-tube', 'test-tube-2', 'test-tubes', 'text',
                'text-cursor', 'text-cursor-input', 'text-quote', 'text-search', 'text-select', 'theater',
                'thermometer', 'thermometer-snowflake', 'thermometer-sun', 'thumbs-down', 'thumbs-up', 'ticket',
                'timer', 'timer-off', 'timer-reset', 'toggle-left', 'toggle-right', 'tornado', 'torus',
                'touchpad', 'touchpad-off', 'tower-control', 'toy-brick', 'tractor', 'traffic-cone', 'train',
                'train-front', 'train-front-tunnel', 'train-track', 'tram-front', 'trash', 'trash-2',
                'tree-deciduous', 'tree-palm', 'tree-pine', 'trees', 'trending-down', 'trending-up',
                'triangle', 'triangle-alert', 'triangle-right', 'trophy', 'truck', 'turtle', 'tv', 'tv-2',
                'twitch', 'twitter', 'type',
                'umbrella', 'umbrella-off', 'underline', 'undo', 'undo-2', 'undo-dot', 'unfold-horizontal',
                'unfold-vertical', 'ungroup', 'university', 'unlink', 'unlink-2', 'unlock', 'unlock-keyhole',
                'unplug', 'upload', 'upload-cloud', 'usb', 'user', 'user-check', 'user-cog', 'user-minus',
                'user-plus', 'user-round', 'user-round-check', 'user-round-cog', 'user-round-minus',
                'user-round-pen', 'user-round-plus', 'user-round-search', 'user-round-x', 'user-search',
                'user-x', 'users', 'users-round', 'utensils', 'utensils-crossed', 'utility-pole',
                'variable', 'vault', 'vegan', 'venetian-mask', 'vibrate', 'vibrate-off', 'video', 'video-off',
                'videotape', 'view', 'voicemail', 'volume', 'volume-1', 'volume-2', 'volume-x', 'vote',
                'wallet', 'wallet-cards', 'wallet-minimal', 'wallpaper', 'wand', 'wand-2', 'warehouse',
                'washing-machine',
                'watch', 'waves', 'waypoints', 'webcam', 'webhook', 'weight', 'wheat', 'wheat-off',
                'whole-word',
                'wifi', 'wifi-off', 'wind', 'wine', 'wine-off', 'workflow', 'worm', 'wrap-text', 'wrench',
                'x', 'x-circle', 'x-octagon', 'x-square',
                'youtube',
                'zap', 'zap-off', 'zoom-in', 'zoom-out',
            ];

            function renderSuggestions(query) {
                if (!query || !allNames.length) {
                    dropdown.classList.add('hidden');
                    return;
                }
                const q = query.toLowerCase();
                const matches = allNames.filter(n => n.includes(q)).slice(0, 30);
                if (!matches.length) {
                    dropdown.classList.add('hidden');
                    return;
                }

                dropdown.innerHTML = matches.map((name, i) =>
                    `<div class="icon-sug flex items-center gap-2.5 px-3 py-2 cursor-pointer hover:bg-surface-50 text-sm" data-name="${name}">
                <i data-lucide="${name}" class="w-4 h-4 text-surface-600 flex-shrink-0"></i>
                <span class="truncate">${name}</span>
            </div>`
                ).join('');
                dropdown.classList.remove('hidden');
                activeIdx = -1;
                if (window.lucide) lucide.createIcons({
                    nodes: dropdown.querySelectorAll('[data-lucide]')
                });
            }

            function pickIcon(name) {
                input.value = name;
                dropdown.classList.add('hidden');
                refreshIconPreview();
            }

            let debounce;
            input.addEventListener('input', () => {
                clearTimeout(debounce);
                debounce = setTimeout(() => {
                    renderSuggestions(input.value.trim());
                    refreshIconPreview();
                }, 150);
            });

            input.addEventListener('keydown', e => {
                const items = dropdown.querySelectorAll('.icon-sug');
                if (!items.length || dropdown.classList.contains('hidden')) return;
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeIdx = Math.min(activeIdx + 1, items.length - 1);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeIdx = Math.max(activeIdx - 1, 0);
                } else if (e.key === 'Enter' && activeIdx >= 0) {
                    e.preventDefault();
                    pickIcon(items[activeIdx].dataset.name);
                    return;
                } else if (e.key === 'Escape') {
                    dropdown.classList.add('hidden');
                    return;
                } else return;
                items.forEach((el, i) => el.classList.toggle('bg-brand-50', i === activeIdx));
                items[activeIdx]?.scrollIntoView({
                    block: 'nearest'
                });
            });

            dropdown.addEventListener('click', e => {
                const row = e.target.closest('.icon-sug');
                if (row) pickIcon(row.dataset.name);
            });

            document.addEventListener('click', e => {
                if (!e.target.closest('#icon-lucide-row')) dropdown.classList.add('hidden');
            });

            input.addEventListener('focus', () => {
                if (input.value.trim()) renderSuggestions(input.value.trim());
            });

            refreshIconPreview();

            // Sync hidden icon_svg field on form submit
            const form = input.closest('form');
            form.addEventListener('submit', () => {
                const isLucide = document.getElementById('icon_lucide_radio').checked;
                const hidden = document.getElementById('icon_svg_hidden');
                if (isLucide) {
                    hidden.value = input.value.trim();
                } else {
                    hidden.value = document.querySelector('[name="icon_svg_raw"]').value;
                }
            });
        });
    </script>
@endpush
