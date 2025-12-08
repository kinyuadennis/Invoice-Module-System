/**
 * Main JavaScript entry point
 * Simple vanilla JS setup for Blade templates
 */
import './bootstrap';
import Alpine from 'alpinejs';

// Initialize Alpine.js
window.Alpine = Alpine;

// Delay Alpine initialization to ensure layout is stable
// This prevents layout shifts that occur when Alpine initializes before CSS is fully loaded
// The delay ensures the DOM and CSS are fully rendered before Alpine processes x-data directives
document.addEventListener('DOMContentLoaded', function() {
    // Small delay to ensure CSS is fully loaded and layout is stable
    // This prevents the "flash" of unstyled content and layout shifts
    setTimeout(() => {
        if (window.Alpine) {
            Alpine.start();
        }
    }, 10);
});
