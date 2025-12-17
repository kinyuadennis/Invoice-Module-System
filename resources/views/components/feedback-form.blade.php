@props([
    'showButton' => true,
])

<div x-data="{ open: false }" class="relative">
    <!-- Feedback Button -->
    @if($showButton)
        <button
            @click="open = true"
            class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all transform hover:scale-110 flex items-center justify-center"
            aria-label="Submit Feedback"
            title="Share Feedback"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
            </svg>
        </button>
    @endif

    <!-- Feedback Modal -->
    <div
        x-show="open"
        x-cloak
        @click.away="open = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div
            @click.stop
            class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 transform transition-all"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-slate-900">Share Your Feedback</h3>
                <button
                    @click="open = false"
                    class="text-slate-400 hover:text-slate-600 transition-colors"
                    aria-label="Close"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form
                x-data="feedbackForm('{{ route('user.feedback.store') }}')"
                @submit.prevent="submit"
                class="space-y-4"
            >
                <!-- Feedback Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-slate-700 mb-2">Type</label>
                    <select
                        id="type"
                        name="type"
                        x-model="form.type"
                        required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-900"
                    >
                        <option value="general">General Feedback</option>
                        <option value="bug">Bug Report</option>
                        <option value="feature">Feature Request</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <!-- Message -->
                <div>
                    <label for="message" class="block text-sm font-medium text-slate-700 mb-2">Message</label>
                    <textarea
                        id="message"
                        name="message"
                        x-model="form.message"
                        rows="4"
                        required
                        minlength="10"
                        maxlength="2000"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-900"
                        placeholder="Tell us what's on your mind..."
                    ></textarea>
                    <p class="mt-1 text-xs text-slate-500">
                        <span x-text="form.message.length"></span> / 2000 characters
                    </p>
                </div>

                <!-- Anonymous -->
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="anonymous"
                        name="anonymous"
                        x-model="form.anonymous"
                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                    >
                    <label for="anonymous" class="ml-2 text-sm text-slate-700">
                        Submit anonymously
                    </label>
                </div>

                <!-- Error Message -->
                <div x-show="error" x-cloak class="p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600" x-text="error"></p>
                </div>

                <!-- Success Message -->
                <div x-show="success" x-cloak class="p-3 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-600" x-text="successMessage"></p>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-3">
                    <button
                        type="button"
                        @click="open = false"
                        class="flex-1 px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="processing"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <span x-show="!processing">Submit</span>
                        <span x-show="processing">Submitting...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function feedbackForm(url) {
            return {
                form: {
                    type: 'general',
                    message: '',
                    anonymous: false,
                },
                error: '',
                success: false,
                successMessage: '',
                processing: false,

                async submit() {
                    this.error = '';
                    this.success = false;
                    this.processing = true;

                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify(this.form),
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'An error occurred');
                        }

                        this.success = true;
                        this.successMessage = data.message || 'Thank you for your feedback!';
                        this.form = { type: 'general', message: '', anonymous: false };

                        setTimeout(() => {
                            this.success = false;
                            this.$dispatch('feedback-submitted');
                            if (this.$el.closest('[x-data*="open"]')) {
                                this.$el.closest('[x-data*="open"]').__x.$data.open = false;
                            }
                        }, 2000);
                    } catch (error) {
                        this.error = error.message || 'Failed to submit feedback. Please try again.';
                    } finally {
                        this.processing = false;
                    }
                },
            };
        }
    </script>
    @endpush
</div>

