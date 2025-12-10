/**
 * Demo Animation Utilities
 * Provides functions for auto-typing, highlighting, and form animations
 */

/**
 * Auto-type text into an input field character by character
 * @param {HTMLElement} element - The input element to type into
 * @param {string} text - The text to type
 * @param {number} speed - Typing speed in milliseconds per character
 * @returns {Promise<void>}
 */
export function autoType(element, text, speed = 50) {
    return new Promise((resolve) => {
        if (!element) {
            resolve();
            return;
        }

        element.value = '';
        element.focus();
        let index = 0;

        const typeInterval = setInterval(() => {
            if (index < text.length) {
                element.value += text[index];
                // Trigger input event for reactive frameworks
                element.dispatchEvent(new Event('input', { bubbles: true }));
                index++;
            } else {
                clearInterval(typeInterval);
                // Trigger change event
                element.dispatchEvent(new Event('change', { bubbles: true }));
                resolve();
            }
        }, speed);
    });
}

/**
 * Highlight an element with a pulsing animation
 * @param {HTMLElement|string} element - Element or selector to highlight
 * @param {number} duration - Duration in milliseconds
 * @returns {Promise<void>}
 */
export function highlightElement(element, duration = 2000) {
    return new Promise((resolve) => {
        const el = typeof element === 'string' ? document.querySelector(element) : element;
        if (!el) {
            resolve();
            return;
        }

        // Add highlight class
        el.classList.add('demo-highlight');
        
        // Scroll into view if needed
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });

        setTimeout(() => {
            el.classList.remove('demo-highlight');
            resolve();
        }, duration);
    });
}

/**
 * Simulate clicking an element
 * @param {HTMLElement|string} element - Element or selector to click
 * @returns {Promise<void>}
 */
export function simulateClick(element) {
    return new Promise((resolve) => {
        const el = typeof element === 'string' ? document.querySelector(element) : element;
        if (!el) {
            resolve();
            return;
        }

        // Add click animation
        el.classList.add('demo-click');
        
        setTimeout(() => {
            el.classList.remove('demo-click');
            // Trigger actual click
            el.click();
            resolve();
        }, 200);
    });
}

/**
 * Fill a form field with animation
 * @param {HTMLElement|string} field - Field element or selector
 * @param {string|number} value - Value to fill
 * @param {boolean} autoType - Whether to use auto-typing animation
 * @returns {Promise<void>}
 */
export function fillField(field, value, autoTypeEnabled = true) {
    return new Promise(async (resolve) => {
        const el = typeof field === 'string' ? document.querySelector(field) : field;
        if (!el) {
            resolve();
            return;
        }

        // Highlight first
        await highlightElement(el, 500);

        // Fill the field
        if (autoTypeEnabled && el.tagName === 'INPUT' && el.type !== 'checkbox' && el.type !== 'radio') {
            await autoType(el, String(value), 30);
        } else {
            if (el.type === 'checkbox') {
                el.checked = Boolean(value);
            } else if (el.type === 'radio') {
                el.checked = true;
            } else if (el.tagName === 'SELECT') {
                el.value = value;
                el.dispatchEvent(new Event('change', { bubbles: true }));
            } else {
                el.value = value;
                el.dispatchEvent(new Event('input', { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        resolve();
    });
}

/**
 * Animate a number counting up
 * @param {HTMLElement|string} element - Element to update
 * @param {number} target - Target number
 * @param {number} duration - Animation duration in milliseconds
 * @param {string} prefix - Prefix to add (e.g., 'KES ')
 * @param {string} suffix - Suffix to add
 * @returns {Promise<void>}
 */
export function animateNumber(element, target, duration = 1000, prefix = '', suffix = '') {
    return new Promise((resolve) => {
        const el = typeof element === 'string' ? document.querySelector(element) : element;
        if (!el) {
            resolve();
            return;
        }

        const start = 0;
        const startTime = performance.now();

        const updateNumber = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function (ease-out)
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(start + (target - start) * easeOut);
            
            el.textContent = `${prefix}${current.toLocaleString('en-KE')}${suffix}`;

            if (progress < 1) {
                requestAnimationFrame(updateNumber);
            } else {
                el.textContent = `${prefix}${target.toLocaleString('en-KE')}${suffix}`;
                resolve();
            }
        };

        requestAnimationFrame(updateNumber);
    });
}

/**
 * Show a loading state on an element
 * @param {HTMLElement|string} element - Element to show loading on
 * @param {number} duration - Duration in milliseconds
 * @returns {Promise<void>}
 */
export function showLoading(element, duration = 1000) {
    return new Promise((resolve) => {
        const el = typeof element === 'string' ? document.querySelector(element) : element;
        if (!el) {
            resolve();
            return;
        }

        el.classList.add('demo-loading');
        el.setAttribute('aria-busy', 'true');

        setTimeout(() => {
            el.classList.remove('demo-loading');
            el.removeAttribute('aria-busy');
            resolve();
        }, duration);
    });
}

/**
 * Wait for a specified duration
 * @param {number} ms - Milliseconds to wait
 * @returns {Promise<void>}
 */
export function wait(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

