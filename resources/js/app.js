import './bootstrap';

import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';

window.Alpine = Alpine;
Alpine.start();

// Initialize Lucide icons
createIcons({ icons });

// Make it available globally for dynamic content (Alpine modals, etc.)
window.lucide = { createIcons: () => createIcons({ icons }) };
