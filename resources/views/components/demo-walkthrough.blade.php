@props(['show' => false])

<div 
    x-data="{
        show: @js($show),
        skipAnimation: false,
        currentStep: 0,
        init() {
            // Listen for demo start event
            window.addEventListener('start-demo', () => {
                this.show = true;
                this.currentStep = 0;
                this.$nextTick(() => {
                    // Initialize demo when modal opens
                    if (window.initDemo) {
                        window.initDemo();
                    }
                });
            });
            
            // Listen for step changes
            window.addEventListener('demo-step-changed', (e) => {
                this.currentStep = e.detail.step;
            });
            
            // Keyboard navigation
            window.addEventListener('keydown', (e) => {
                if (!this.show) return;
                
                if (e.key === 'Escape') {
                    this.close();
                } else if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    this.previousStep();
                } else if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    this.nextStep();
                }
            });
        },
        close() {
            this.show = false;
        },
        toggleAnimation() {
            this.skipAnimation = !this.skipAnimation;
            document.body.classList.toggle('demo-skip-animation', this.skipAnimation);
        },
        nextStep() {
            if (window.nextDemoStep) {
                window.nextDemoStep();
            }
        },
        previousStep() {
            if (window.previousDemoStep) {
                window.previousDemoStep();
            }
        },
        canGoPrevious() {
            return this.currentStep > 0;
        },
        isLastStep() {
            return this.currentStep >= 5;
        }
    }"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
    @keydown.escape.window="close()"
    x-trap.noscroll="show"
    role="dialog"
    aria-modal="true"
    aria-labelledby="demo-title"
>
    <!-- Background overlay -->
    <div 
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 transition-opacity bg-black/70 backdrop-blur-sm"
        @click="close()"
        aria-hidden="true"
    ></div>

    <!-- Modal panel -->
    <div 
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none"
    >
        <div class="inline-block w-full max-w-4xl bg-white rounded-xl shadow-2xl transform transition-all pointer-events-auto max-h-[90vh] overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-xl flex items-center justify-between flex-shrink-0">
                <div>
                    <h2 id="demo-title" class="text-xl font-bold text-white">Interactive Demo</h2>
                    <p class="text-sm text-blue-100 mt-1">See how InvoiceHub works</p>
                </div>
                <div class="flex items-center gap-3">
                    <button 
                        @click="toggleAnimation()"
                        class="text-white/80 hover:text-white text-sm px-3 py-1 rounded-md hover:bg-white/10 transition-colors"
                        :aria-pressed="skipAnimation"
                        aria-label="Toggle animation speed"
                    >
                        <span x-show="!skipAnimation">Skip Animation</span>
                        <span x-show="skipAnimation">Enable Animation</span>
                    </button>
                    <button 
                        @click="close()" 
                        class="text-white/80 hover:text-white focus-visible:outline-2 focus-visible:outline-white focus-visible:outline-offset-2 rounded-md p-1 transition-colors"
                        aria-label="Close demo"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Demo Container -->
            <div class="p-6 demo-container overflow-y-auto flex-1" id="demo-walkthrough-container">
                <!-- Demo content will be injected here -->
            </div>

            <!-- Footer with navigation -->
            <div class="bg-gray-50 px-6 py-4 rounded-b-xl border-t border-gray-200 flex items-center justify-between flex-shrink-0">
                <button 
                    @click="previousStep()"
                    @keydown.left.prevent="previousStep()"
                    :disabled="!canGoPrevious()"
                    class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Previous
                </button>
                <div class="text-xs text-gray-500 flex items-center gap-4">
                    <span class="flex items-center gap-1">
                        <kbd class="px-2 py-1 bg-white border border-gray-300 rounded text-xs">←</kbd>
                        <kbd class="px-2 py-1 bg-white border border-gray-300 rounded text-xs">→</kbd>
                        <span>Navigate</span>
                    </span>
                </div>
                <button 
                    @click="nextStep()"
                    @keydown.right.prevent="nextStep()"
                    x-show="!isLastStep()"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors flex items-center gap-2"
                >
                    Next
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Screen reader announcements -->
<div 
    x-show="show"
    class="sr-only"
    role="status"
    aria-live="polite"
    aria-atomic="true"
    id="demo-announcements"
></div>
