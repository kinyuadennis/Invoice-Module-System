/**
 * Dashboard Tour Component
 * Interactive walkthrough for new users
 */

export function initDashboardTour() {
    // Check if user has completed onboarding and hasn't seen the tour
    const tourSeen = localStorage.getItem('dashboard-tour-seen');
    const onboardingCompleted = document.body.dataset.onboardingCompleted === 'true';
    
    // Only show tour if onboarding is completed and tour hasn't been seen
    if (!onboardingCompleted || tourSeen === 'true') {
        return;
    }

    // Simple tour using native browser features
    const steps = [
        {
            target: '#company-switcher',
            title: 'Company Switcher',
            content: 'Switch between your companies using this selector. Press ⌘K (Mac) or Ctrl+K (Windows) for quick access.',
            position: 'bottom'
        },
        {
            target: '#dashboard-new-invoice-btn',
            title: 'Create Invoice',
            content: 'Click here to create your first invoice. You can add clients, items, and send invoices directly from here.',
            position: 'bottom'
        },
        {
            target: '.grid.grid-cols-1.gap-6',
            title: 'Dashboard Metrics',
            content: 'Track your revenue, outstanding amounts, and invoice status at a glance.',
            position: 'top'
        }
    ];

    let currentStep = 0;
    let overlay = null;
    let tooltip = null;

    function createOverlay() {
        overlay = document.createElement('div');
        overlay.id = 'tour-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9998;
            pointer-events: none;
        `;
        document.body.appendChild(overlay);
    }

    function createTooltip(step) {
        if (tooltip) {
            tooltip.remove();
        }

        const target = document.querySelector(step.target);
        if (!target) {
            return;
        }

        const rect = target.getBoundingClientRect();
        tooltip = document.createElement('div');
        tooltip.id = 'tour-tooltip';
        tooltip.style.cssText = `
            position: fixed;
            z-index: 9999;
            background: white;
            border-radius: 8px;
            padding: 20px;
            max-width: 320px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        `;

        const isBottom = step.position === 'bottom';
        const top = isBottom ? rect.bottom + 10 : rect.top - 200;
        const left = rect.left + (rect.width / 2) - 160;

        tooltip.style.top = `${Math.max(10, top)}px`;
        tooltip.style.left = `${Math.max(10, Math.min(left, window.innerWidth - 330))}px`;

        tooltip.innerHTML = `
            <div class="flex items-start justify-between mb-3">
                <h3 class="text-lg font-semibold text-gray-900">${step.title}</h3>
                <button onclick="window.closeTour()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <p class="text-sm text-gray-600 mb-4">${step.content}</p>
            <div class="flex items-center justify-between">
                <div class="flex gap-2">
                    ${steps.map((_, i) => `
                        <div class="w-2 h-2 rounded-full ${i === currentStep ? 'bg-[#2B6EF6]' : 'bg-gray-300'}"></div>
                    `).join('')}
                </div>
                <div class="flex gap-2">
                    ${currentStep > 0 ? `
                        <button onclick="window.tourPrev()" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                            Previous
                        </button>
                    ` : ''}
                    ${currentStep < steps.length - 1 ? `
                        <button onclick="window.tourNext()" class="px-3 py-1.5 text-sm font-medium text-white bg-[#2B6EF6] rounded-lg hover:bg-[#2563EB]">
                            Next
                        </button>
                    ` : `
                        <button onclick="window.closeTour()" class="px-3 py-1.5 text-sm font-medium text-white bg-[#2B6EF6] rounded-lg hover:bg-[#2563EB]">
                            Got it!
                        </button>
                    `}
                </div>
            </div>
        `;

        document.body.appendChild(tooltip);

        // Highlight target
        target.style.position = 'relative';
        target.style.zIndex = '10000';
        target.style.transition = 'all 0.3s';
        target.style.transform = 'scale(1.05)';
        target.style.boxShadow = '0 0 0 4px rgba(43, 110, 246, 0.3)';
    }

    function showStep(index) {
        if (index < 0 || index >= steps.length) {
            closeTour();
            return;
        }

        currentStep = index;
        const step = steps[index];
        createTooltip(step);
    }

    window.tourNext = function() {
        showStep(currentStep + 1);
    };

    window.tourPrev = function() {
        showStep(currentStep - 1);
    };

    window.closeTour = function() {
        if (overlay) overlay.remove();
        if (tooltip) tooltip.remove();
        
        // Reset all highlights
        steps.forEach(s => {
            const target = document.querySelector(s.target);
            if (target) {
                target.style.transform = '';
                target.style.boxShadow = '';
            }
        });

        localStorage.setItem('dashboard-tour-seen', 'true');
    };

    // Start tour
    createOverlay();
    showStep(0);

    // Add restart button to dashboard (optional)
    const restartButton = document.createElement('button');
    restartButton.textContent = 'Take Tour';
    restartButton.className = 'text-xs text-gray-500 hover:text-gray-700';
    restartButton.onclick = () => {
        localStorage.removeItem('dashboard-tour-seen');
        window.location.reload();
    };
    
    // Add to page header if needed
    const header = document.querySelector('.flex.items-center.justify-between');
    if (header) {
        const restartDiv = document.createElement('div');
        restartDiv.appendChild(restartButton);
        header.appendChild(restartDiv);
    }
}

