<div 
    x-data="invoiceNumberConfig({{ json_encode($company) }})"
    x-show="open"
    @open-invoice-config.window="open = true"
    @close-modal.window="open = false"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
>
    <!-- Overlay -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="open = false"></div>

    <!-- Modal -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full p-6" @click.stop>
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Configure Invoice Number Preferences</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Info Section -->
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-gray-700 mb-3">
                    Configure multiple transaction number series to auto-generate transaction numbers with unique prefixes according to your business needs.
                </p>
                <button 
                    @click="showAdvancedConfig = !showAdvancedConfig"
                    class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                >
                    Configure →
                </button>
            </div>

            <!-- Main Content -->
            <div class="space-y-6">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-700 mb-4">
                        Your invoice numbers are set on auto-generate mode to save your time. Are you sure about changing this setting?
                    </p>

                    <!-- Auto-generate Option -->
                    <div class="space-y-4">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input 
                                type="radio" 
                                name="invoice_mode" 
                                value="auto"
                                x-model="config.mode"
                                @change="updatePreview()"
                                class="mt-1"
                            >
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">Continue auto-generating invoice numbers</div>
                                
                                <!-- Prefix Configuration -->
                                <div class="mt-3 space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Prefix</label>
                                        <div class="flex gap-2">
                                            <input 
                                                type="text"
                                                x-model="config.prefix"
                                                @input="updatePreview()"
                                                placeholder="INV"
                                                class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            >
                                            <button 
                                                @click="showPrefixPlaceholders = !showPrefixPlaceholders"
                                                class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                                                type="button"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                </svg>
                                            </button>
                                        </div>
                                        
                                        <!-- Placeholder Dropdown -->
                                        <div 
                                            x-show="showPrefixPlaceholders"
                                            @click.away="showPrefixPlaceholders = false"
                                            class="mt-2 bg-white border border-gray-300 rounded-lg shadow-lg p-2 space-y-1"
                                            x-cloak
                                        >
                                            <button 
                                                @click="insertPlaceholder('%YYYY%')"
                                                class="w-full text-left px-3 py-2 hover:bg-gray-50 rounded text-sm"
                                                type="button"
                                            >
                                                Fiscal Year Start
                                            </button>
                                            <button 
                                                @click="insertPlaceholder('%YYYY%')"
                                                class="w-full text-left px-3 py-2 hover:bg-gray-50 rounded text-sm"
                                                type="button"
                                            >
                                                Fiscal Year End
                                            </button>
                                            <button 
                                                @click="insertPlaceholder('%YYYY%')"
                                                class="w-full text-left px-3 py-2 hover:bg-gray-50 rounded text-sm"
                                                type="button"
                                            >
                                                Transaction Year
                                            </button>
                                            <button 
                                                @click="insertPlaceholder('%MM%')"
                                                class="w-full text-left px-3 py-2 hover:bg-gray-50 rounded text-sm"
                                                type="button"
                                            >
                                                Transaction Month
                                            </button>
                                            <button 
                                                @click="insertPlaceholder('%DD%')"
                                                class="w-full text-left px-3 py-2 hover:bg-gray-50 rounded text-sm"
                                                type="button"
                                            >
                                                Transaction Date
                                            </button>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Next Number</label>
                                        <div class="flex gap-2">
                                            <input 
                                                type="text"
                                                x-model="config.next_number"
                                                @input="updatePreview()"
                                                placeholder="000001"
                                                class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            >
                                            <button 
                                                @click="showNumberOptions = !showNumberOptions"
                                                class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                                                type="button"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <!-- Reset Options -->
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input 
                                type="radio" 
                                name="invoice_mode" 
                                value="fiscal_reset"
                                x-model="config.mode"
                                @change="updatePreview()"
                                class="mt-1"
                            >
                            <div class="font-medium text-gray-900">Reset at the start of each fiscal year</div>
                        </label>

                        <label class="flex items-start gap-3 cursor-pointer">
                            <input 
                                type="radio" 
                                name="invoice_mode" 
                                value="manual"
                                x-model="config.mode"
                                @change="updatePreview()"
                                class="mt-1"
                            >
                            <div class="font-medium text-gray-900">Enter manually (MM/yyyy format)</div>
                        </label>
                    </div>
                </div>

                <!-- Preview -->
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <label class="block text-sm font-medium text-blue-900 mb-2">Preview</label>
                    <div class="text-lg font-bold text-blue-600" x-text="preview"></div>
                    <p class="mt-2 text-xs text-blue-700">This is how your next invoice number will look</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex gap-3 justify-end">
                <button 
                    @click="open = false"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium"
                >
                    Cancel
                </button>
                <button 
                    @click="saveConfig()"
                    :disabled="saving"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium disabled:opacity-50"
                >
                    <span x-show="!saving">Save</span>
                    <span x-show="saving">Saving...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function invoiceNumberConfig(company) {
    return {
        open: false,
        saving: false,
        showPrefixPlaceholders: false,
        showNumberOptions: false,
        showAdvancedConfig: false,
        config: {
            mode: 'auto',
            prefix: company.invoice_prefix || 'INV',
            next_number: '000001',
            reset_fiscal_year: false,
        },
        preview: '',

        init() {
            this.updatePreview();
        },

        insertPlaceholder(placeholder) {
            const input = event.target.closest('div').previousElementSibling.querySelector('input');
            const start = input.selectionStart;
            const end = input.selectionEnd;
            this.config.prefix = this.config.prefix.substring(0, start) + placeholder + this.config.prefix.substring(end);
            this.showPrefixPlaceholders = false;
            this.updatePreview();
        },

        updatePreview() {
            let preview = this.config.prefix || 'INV';
            
            // Replace placeholders
            const now = new Date();
            preview = preview.replace('%YYYY%', now.getFullYear());
            preview = preview.replace('%YY%', String(now.getFullYear()).slice(-2));
            preview = preview.replace('%MM%', String(now.getMonth() + 1).padStart(2, '0'));
            preview = preview.replace('%DD%', String(now.getDate()).padStart(2, '0'));
            
            preview += '-' + (this.config.next_number || '000001');
            
            this.preview = preview;
        },

        async saveConfig() {
            this.saving = true;
            try {
                // Use FormData to match Laravel's expected format
                const formData = new FormData();
                formData.append('invoice_prefix', this.config.prefix || 'INV');
                const padding = this.config.next_number && this.config.next_number.length > 0 
                    ? this.config.next_number.length 
                    : {{ $company->invoice_padding ?? 4 }};
                formData.append('invoice_padding', padding.toString());
                formData.append('invoice_format', '{{ $company->invoice_format ?? "{PREFIX}-{NUMBER}" }}');
                
                const response = await fetch('{{ route("user.company.update-invoice-format") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    alert('Failed to save preferences: ' + errorText);
                    this.saving = false;
                    return;
                }

                let data;
                try {
                    data = await response.json();
                } catch (e) {
                    console.error('Error parsing response:', e);
                    alert('Failed to save preferences. Please try again.');
                    this.saving = false;
                    return;
                }

                if (data.success) {
                    this.open = false;
                    // Emit event with new invoice number so invoice builder can update immediately
                    window.dispatchEvent(new CustomEvent('invoice-config-updated', {
                        detail: {
                            prefix: data.active_prefix || this.config.prefix,
                            next_invoice_number: data.next_invoice_number
                        }
                    }));
                    // Show success message
                    alert('Invoice number preferences saved successfully!');
                } else {
                    alert('Failed to save preferences: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error saving config:', error);
                alert('Failed to save preferences. Please try again.');
            } finally {
                this.saving = false;
            }
        }
    }
}
</script>

