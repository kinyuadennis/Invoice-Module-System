<div 
    x-data="clientCreateModal()"
    x-show="open"
    @open-create-client-modal.window="open = true"
    @close-create-client-modal.window="open = false"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
>
    <!-- Overlay -->
    <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" @click="open = false"></div>

    <!-- Modal -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6" @click.stop>
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900">Add New Client</h3>
                <button @click="open = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="createClient" novalidate>
                <!-- General Error Message -->
                <div x-show="errors.general" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg" x-cloak>
                    <p class="text-sm text-red-600" x-text="errors.general"></p>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-1">Name *</label>
                        <input 
                            type="text" 
                            x-model="form.name"
                            required
                            class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            placeholder="Client name"
                        >
                        <p x-show="errors.name" class="mt-1 text-sm text-red-600" x-text="errors.name"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-1">Email</label>
                        <input 
                            type="email" 
                            x-model="form.email"
                            class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            placeholder="client@example.com"
                        >
                        <p x-show="errors.email" class="mt-1 text-sm text-red-600" x-text="errors.email"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-1">Phone</label>
                        <input 
                            type="text" 
                            x-model="form.phone"
                            class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            placeholder="+254 700 000 000"
                        >
                        <p x-show="errors.phone" class="mt-1 text-sm text-red-600" x-text="errors.phone"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-1">Address</label>
                        <textarea 
                            x-model="form.address"
                            rows="2"
                            class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            placeholder="Client address"
                        ></textarea>
                        <p x-show="errors.address" class="mt-1 text-sm text-red-600" x-text="errors.address"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-900 mb-1">KRA PIN</label>
                        <input 
                            type="text" 
                            x-model="form.kra_pin"
                            class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            placeholder="A012345678B"
                            maxlength="11"
                        >
                        <p class="mt-1 text-xs text-gray-500">Format: Letter + 9 digits + Letter (e.g., A012345678B)</p>
                        <p x-show="errors.kra_pin" class="mt-1 text-sm text-red-600" x-text="errors.kra_pin"></p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 flex gap-3">
                    <button 
                        type="button"
                        @click="open = false"
                        class="flex-1 px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors font-semibold"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit"
                        :disabled="processing || !form.name || form.name.trim() === ''"
                        class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span x-show="!processing">Create Client</span>
                        <span x-show="processing">Creating...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function clientCreateModal() {
    return {
        open: false,
        processing: false,
        form: {
            name: '',
            email: '',
            phone: '',
            address: '',
            kra_pin: ''
        },
        errors: {},
        
        init() {
            // Watch for modal opening to reset form
            this.$watch('open', (value) => {
                if (value) {
                    this.resetForm();
                }
            });
        },
        
        async createClient() {
            this.processing = true;
            this.errors = {};
            
            try {
                const response = await fetch('{{ route("user.clients.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    // Try to parse error response
                    let errorData;
                    try {
                        errorData = await response.json();
                    } catch (e) {
                        errorData = { message: 'Failed to create client. Please try again.' };
                    }
                    
                    if (errorData.errors) {
                        // Handle Laravel validation errors
                        this.errors = {};
                        Object.keys(errorData.errors).forEach(key => {
                            this.errors[key] = Array.isArray(errorData.errors[key]) 
                                ? errorData.errors[key][0] 
                                : errorData.errors[key];
                        });
                    } else {
                        this.errors = { general: errorData.message || 'Failed to create client. Please try again.' };
                    }
                    this.processing = false;
                    return;
                }
                
                if (data.success && data.client) {
                    // Success - emit event with new client
                    window.dispatchEvent(new CustomEvent('client-created', { detail: data.client }));
                    
                    // Close modal
                    this.open = false;
                    this.resetForm();
                } else {
                    this.errors = { general: data.message || 'Failed to create client' };
                }
            } catch (error) {
                console.error('Error creating client:', error);
                this.errors = { general: 'Network error. Please check your connection and try again.' };
                this.processing = false;
            }
        },
        
        resetForm() {
            this.form = {
                name: '',
                email: '',
                phone: '',
                address: '',
                kra_pin: ''
            };
            this.errors = {};
        }
    }
}
</script>

