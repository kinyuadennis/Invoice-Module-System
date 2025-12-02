<div 
    x-data="clientCreateModal()"
    x-show="open"
    @open-client-modal.window="open = true"
    @close-modal.window="open = false"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
>
    <!-- Overlay -->
    <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" @click="open = false"></div>

    <!-- Modal -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6" @click.stop>
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
                        :disabled="processing"
                        class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors font-semibold disabled:opacity-50"
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
            address: ''
        },
        errors: {},
        
        async createClient() {
            this.processing = true;
            this.errors = {};
            
            try {
                const response = await fetch('{{ route("user.clients.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.form)
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        this.errors = { general: data.message || 'Failed to create client' };
                    }
                    this.processing = false;
                    return;
                }
                
                // Success - emit event with new client
                this.$dispatch('client-created', data.client);
                this.open = false;
                this.resetForm();
            } catch (error) {
                this.errors = { general: 'Network error. Please try again.' };
            } finally {
                this.processing = false;
            }
        },
        
        resetForm() {
            this.form = {
                name: '',
                email: '',
                phone: '',
                address: ''
            };
            this.errors = {};
        }
    }
}
</script>

