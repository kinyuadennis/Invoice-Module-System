<div x-data="invoiceActions()" class="space-y-4">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Save Draft -->
        <button
            type="button"
            @click="saveDraft()"
            :disabled="processing"
            class="flex items-center justify-center px-6 py-3 border-2 border-gray-300 rounded-lg text-gray-700 dark:text-gray-200 hover:border-gray-400 hover:bg-gray-50 transition-colors font-semibold disabled:opacity-50"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
            <span x-show="!processing">Save Draft</span>
            <span x-show="processing">Saving...</span>
        </button>

        <!-- Generate PDF -->
        <button
            type="button"
            @click="generatePdf()"
            :disabled="processing"
            class="flex items-center justify-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold disabled:opacity-50"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span x-show="!processing">Generate PDF</span>
            <span x-show="processing">Generating...</span>
        </button>

        <!-- Send Email -->
        <button
            type="button"
            @click="sendEmail()"
            :disabled="processing"
            class="flex items-center justify-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-semibold disabled:opacity-50"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <span x-show="!processing">Send Email</span>
            <span x-show="processing">Sending...</span>
        </button>

        <!-- Send WhatsApp -->
        <button
            type="button"
            @click="sendWhatsApp()"
            :disabled="processing"
            class="flex items-center justify-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold disabled:opacity-50"
        >
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
            </svg>
            <span x-show="!processing">Send WhatsApp</span>
            <span x-show="processing">Sending...</span>
        </button>

        <!-- M-PESA (Coming Soon) -->
        <button
            type="button"
            disabled
            class="flex items-center justify-center px-6 py-3 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed font-semibold relative"
        >
            <span class="text-xs absolute -top-2 -right-2 bg-yellow-400 text-yellow-900 px-2 py-0.5 rounded-full font-bold">Coming Soon</span>
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
            Send via M-PESA
        </button>
    </div>

    <!-- Success/Error Messages -->
    <div x-show="message" x-transition class="mt-4 p-4 rounded-lg" :class="messageType === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
        <p class="text-sm font-medium" :class="messageType === 'success' ? 'text-green-800' : 'text-red-800'" x-text="message"></p>
    </div>
</div>

<script>
function invoiceActions() {
    return {
        processing: false,
        message: '',
        messageType: 'success',
        invoiceId: null,
        
        async saveDraft() {
            this.processing = true;
            this.message = '';
            
            try {
                // Get wizard instance
                const wizard = document.querySelector('[x-data*="invoiceWizard"]');
                if (!wizard || !wizard._x_dataStack || !wizard._x_dataStack[0]) {
                    this.showMessage('Unable to access form data. Please refresh and try again.', 'error');
                    this.processing = false;
                    return;
                }
                
                const wizardData = wizard._x_dataStack[0];
                
                // Prepare FormData (Laravel expects form data, not JSON)
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                formData.append('client_id', wizardData.formData.client_id || '');
                formData.append('issue_date', wizardData.formData.issue_date || '');
                formData.append('due_date', wizardData.formData.due_date || '');
                formData.append('invoice_reference', wizardData.formData.invoice_reference || '');
                formData.append('notes', wizardData.formData.notes || '');
                formData.append('payment_method', wizardData.formData.payment_method || '');
                formData.append('payment_details', wizardData.formData.payment_details || '');
                formData.append('status', 'draft');
                
                // Add items
                if (wizardData.formData.items && wizardData.formData.items.length > 0) {
                    wizardData.formData.items.forEach((item, index) => {
                        formData.append(`items[${index}][description]`, item.description || '');
                        formData.append(`items[${index}][quantity]`, item.quantity || 1);
                        formData.append(`items[${index}][unit_price]`, item.unit_price || 0);
                        formData.append(`items[${index}][total_price]`, item.total_price || 0);
                    });
                }
                
                const response = await fetch('{{ route("user.invoices.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                    redirect: 'follow'
                });
                
                // Check response type
                const contentType = response.headers.get('content-type');
                let data = null;
                
                if (contentType && contentType.includes('application/json')) {
                    data = await response.json();
                } else {
                    // HTML response (redirect) - treat as success
                    if (response.ok || response.status === 200 || response.status === 302) {
                        this.showMessage('Invoice saved as draft successfully!', 'success');
                        setTimeout(() => {
                            window.location.href = '{{ route("user.invoices.index") }}';
                        }, 1500);
                        return;
                    }
                }
                
                if (response.ok || response.status === 200) {
                    this.showMessage(data?.message || 'Invoice saved as draft successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = data?.redirect || '{{ route("user.invoices.index") }}';
                    }, 1500);
                } else {
                    // Handle validation errors
                    let errorMsg = 'Failed to save invoice';
                    if (data?.errors) {
                        errorMsg = Object.values(data.errors).flat().join(', ');
                    } else if (data?.message) {
                        errorMsg = data.message;
                    }
                    this.showMessage(errorMsg, 'error');
                }
            } catch (error) {
                console.error('Save draft error:', error);
                this.showMessage('An error occurred: ' + (error.message || 'Please try again'), 'error');
            } finally {
                this.processing = false;
            }
        },
        
        async generatePdf() {
            this.processing = true;
            this.message = '';
            
            try {
                // First, save the invoice if not already saved
                const wizard = document.querySelector('[x-data*="invoiceWizard"]');
                if (!wizard || !wizard._x_dataStack || !wizard._x_dataStack[0]) {
                    this.showMessage('Please save the invoice first', 'error');
                    this.processing = false;
                    return;
                }
                
                const wizardData = wizard._x_dataStack[0];
                
                // Check if we have an invoice ID (from a previous save)
                if (!this.invoiceId) {
                    // Save as draft first, then generate PDF
                    this.showMessage('Saving invoice first...', 'info');
                    await this.saveDraft();
                    // After save, the page will redirect, so PDF generation will happen on the invoice show page
                    return;
                }
                
                // Generate PDF for existing invoice
                const response = await fetch(`/app/invoices/${this.invoiceId}/pdf`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                
                if (response.ok) {
                    // Download the PDF
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `invoice-${this.invoiceId}.pdf`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    this.showMessage('PDF generated successfully!', 'success');
                } else {
                    this.showMessage('Failed to generate PDF', 'error');
                }
            } catch (error) {
                console.error('PDF generation error:', error);
                this.showMessage('Error generating PDF: ' + (error.message || 'Please try again'), 'error');
            } finally {
                this.processing = false;
            }
        },
        
        async sendEmail() {
            // This will be implemented after invoice is created
            this.showMessage('Email sending will be available after saving the invoice', 'info');
        },
        
        async sendWhatsApp() {
            // This will be implemented after invoice is created
            this.showMessage('WhatsApp sending will be available after saving the invoice', 'info');
        },
        
        getFormData() {
            // Get all form data from wizard state
            // This will be connected to the main wizard component
            return window.wizardFormData || {};
        },
        
        showMessage(text, type) {
            this.message = text;
            this.messageType = type;
            setTimeout(() => {
                this.message = '';
            }, 5000);
        }
    }
}
</script>

