@props(['clients', 'services', 'company' => null, 'nextInvoiceNumber' => null])

<div 
    x-data="invoiceWizard({{ json_encode($clients) }}, {{ json_encode($services) }})"
    class="max-w-5xl mx-auto"
>
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900">Create Invoice</h1>
        <p class="mt-1 text-sm text-slate-600">Follow the steps below to create your invoice</p>
    </div>

    <!-- Step Indicator -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <template x-for="i in 6" :key="i">
                <div class="flex items-center flex-1">
                    <!-- Step Circle -->
                    <div class="flex flex-col items-center flex-1">
                        <div 
                            :class="{
                                'bg-blue-500 border-blue-500 text-white': i < currentStep,
                                'bg-blue-500 border-blue-500 text-white ring-4 ring-blue-100': i === currentStep,
                                'bg-white border-slate-300 text-slate-400': i > currentStep
                            }"
                            class="flex items-center justify-center w-10 h-10 rounded-full border-2 transition-all duration-300"
                        >
                            <template x-if="i < currentStep">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </template>
                            <template x-if="i >= currentStep">
                                <span class="text-sm font-bold" x-text="i"></span>
                            </template>
                        </div>
                        <!-- Step Label (hidden on mobile) -->
                        <span 
                            :class="i <= currentStep ? 'text-blue-600' : 'text-slate-400'"
                            class="mt-2 text-xs font-medium hidden sm:block"
                        >
                            Step <span x-text="i"></span>
                        </span>
                    </div>
                    
                    <!-- Connector Line -->
                    <template x-if="i < 6">
                        <div 
                            :class="i < currentStep ? 'bg-blue-500' : 'bg-slate-300'"
                            class="flex-1 h-0.5 mx-2 transition-colors duration-300"
                        ></div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <!-- Client Create Modal (Outside form to prevent validation conflicts) -->
    <x-client-create-modal />

    <!-- Form -->
    <form method="POST" action="{{ route('user.invoices.store') }}" @submit.prevent="submitForm" novalidate>
        @csrf
        
        <!-- Hidden inputs for form data -->
        <input type="hidden" name="client_id" x-model="formData.client_id">
        <input type="hidden" name="issue_date" x-model="formData.issue_date">
        <input type="hidden" name="due_date" x-model="formData.due_date">
        <input type="hidden" name="invoice_reference" x-model="formData.invoice_reference">
        <input type="hidden" name="notes" x-model="formData.notes">
        <input type="hidden" name="payment_method" x-model="formData.payment_method">
        <input type="hidden" name="payment_details" x-model="formData.payment_details">
        <input type="hidden" name="status" x-model="formData.status">

        <!-- Step Content -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 md:p-8 min-h-[400px]">
            <!-- Step 1: Client Selection -->
            <div x-show="currentStep === 1" x-transition>
                <h2 class="text-2xl font-bold text-slate-900 mb-6">Step 1: Select Client</h2>
                <x-client-selector :clients="$clients" />
            </div>

            <!-- Step 2: Invoice Details -->
            <div x-show="currentStep === 2" x-transition>
                <h2 class="text-2xl font-bold text-slate-900 mb-6">Step 2: Invoice Details</h2>
                @if($nextInvoiceNumber)
                    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm font-medium text-blue-900 mb-1">Next Invoice Number</p>
                        <p class="text-xl font-bold text-blue-600">{{ $nextInvoiceNumber }}</p>
                        <p class="text-xs text-blue-700 mt-1">This number will be assigned when you create the invoice</p>
                    </div>
                @endif
                <div x-data="{ get formData() { return $el.closest('[x-data*=\'invoiceWizard\']')._x_dataStack[0].formData; }, get currentStep() { return $el.closest('[x-data*=\'invoiceWizard\']')._x_dataStack[0].currentStep; }, get validationErrors() { return $el.closest('[x-data*=\'invoiceWizard\']')._x_dataStack[0].validationErrors; } }">
                    <x-invoice-details-form :company="$company" />
                </div>
            </div>

            <!-- Step 3: Line Items -->
            <div x-show="currentStep === 3" x-transition>
                <h2 class="text-2xl font-bold text-slate-900 mb-6">Step 3: Add Line Items</h2>
                <div x-data="{ get currentStep() { return $el.closest('[x-data*=\'invoiceWizard\']')._x_dataStack[0].currentStep; } }">
                    <x-line-items-editor :services="$services" />
                </div>
            </div>

            <!-- Step 4: Summary -->
            <div x-show="currentStep === 4" x-transition>
                <h2 class="text-2xl font-bold text-slate-900 mb-6">Step 4: Review Summary</h2>
                <x-invoice-summary />
            </div>

            <!-- Step 5: Payment Method -->
            <div x-show="currentStep === 5" x-transition>
                <h2 class="text-2xl font-bold text-slate-900 mb-6">Step 5: Payment Method</h2>
                <x-payment-method-selector />
            </div>

            <!-- Step 6: Save & Send -->
            <div x-show="currentStep === 6" x-transition>
                <h2 class="text-2xl font-bold text-slate-900 mb-6">Step 6: Save & Send</h2>
                <x-invoice-actions />
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="mt-6 flex items-center justify-between">
            <button
                type="button"
                @click="previousStep()"
                x-show="currentStep > 1"
                class="px-6 py-3 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 transition-colors font-semibold"
            >
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Previous
            </button>

            <div class="flex-1"></div>

            <button
                type="button"
                @click="nextStep()"
                x-show="currentStep < 6"
                :disabled="processing"
                class="px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span x-show="!processing">Next</span>
                <span x-show="processing">Validating...</span>
                <svg class="w-5 h-5 inline ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </form>
</div>

<script>
function invoiceWizard(clients, services) {
    return {
        currentStep: 1,
        formData: {
            client_id: null,
            issue_date: new Date().toISOString().split('T')[0],
            due_date: '',
            invoice_reference: '',
            notes: '',
            payment_method: null,
            payment_details: '',
            status: 'draft',
            items: []
        },
        validationErrors: {},
        clients: clients,
        services: services,
        processing: false,
        
        init() {
            // Generate invoice reference
            this.generateInvoiceReference();
            
            // Listen for events from child components
            window.addEventListener('client-selected', (e) => {
                this.formData.client_id = e.detail.id;
            });
            
            window.addEventListener('client-created', (e) => {
                this.clients.push(e.detail);
                this.formData.client_id = e.detail.id;
            });
            
            window.addEventListener('details-changed', (e) => {
                Object.assign(this.formData, e.detail);
            });
            
            window.addEventListener('items-updated', (e) => {
                this.formData.items = e.detail.map((item, index) => ({
                    description: item.description,
                    quantity: item.quantity,
                    unit_price: item.unit_price,
                    total_price: item.total_price
                }));
            });
            
            window.addEventListener('payment-method-selected', (e) => {
                this.formData.payment_method = e.detail.method;
                this.formData.payment_details = e.detail.details || '';
            });
            
            // Listen for step changes and broadcast to children
            this.$watch('currentStep', (step) => {
                window.dispatchEvent(new CustomEvent('step-changed', { detail: { step } }));
            });
            
            // Handle wizard data requests from children
            window.addEventListener('request-wizard-data', () => {
                window.dispatchEvent(new CustomEvent('wizard-data-sync', { 
                    detail: { formData: this.formData, currentStep: this.currentStep } 
                }));
            });
            
            // Store form data globally for actions component
            window.wizardFormData = this.formData;
            
            // Broadcast initial step
            window.dispatchEvent(new CustomEvent('step-changed', { detail: { step: this.currentStep } }));
        },
        
        generateInvoiceReference() {
            const year = new Date().getFullYear();
            // This will be properly generated on the backend, but show a preview
            this.formData.invoice_reference = `INV-${year}-0001`;
        },
        
        validateStep() {
            this.validationErrors = {};
            let isValid = true;
            
            try {
                switch(this.currentStep) {
                    case 1:
                        if (!this.formData.client_id) {
                            this.validationErrors.client_id = 'Please select a client';
                            isValid = false;
                        }
                        break;
                    case 2:
                        if (!this.formData.issue_date) {
                            this.validationErrors.issue_date = 'Issue date is required';
                            isValid = false;
                        }
                        if (!this.formData.due_date) {
                            this.validationErrors.due_date = 'Due date is required';
                            isValid = false;
                        }
                        if (this.formData.issue_date && this.formData.due_date) {
                            if (new Date(this.formData.due_date) < new Date(this.formData.issue_date)) {
                                this.validationErrors.due_date = 'Due date must be after issue date';
                                isValid = false;
                            }
                        }
                        break;
                    case 3:
                        if (!this.formData.items || this.formData.items.length === 0) {
                            this.validationErrors.items = 'Please add at least one line item';
                            isValid = false;
                        } else {
                            const invalidItems = this.formData.items.filter(item => 
                                !item.description || !item.description.trim() || 
                                !item.unit_price || item.unit_price <= 0
                            );
                            if (invalidItems.length > 0) {
                                this.validationErrors.items = 'All items must have description and valid price';
                                isValid = false;
                            }
                        }
                        break;
                }
            } catch (error) {
                console.error('Validation error:', error);
                isValid = false;
            }
            
            return isValid;
        },
        
        nextStep() {
            this.processing = true;
            
            // Small delay to allow UI to update
            setTimeout(() => {
                try {
                    if (this.validateStep()) {
                        if (this.currentStep < 6) {
                            this.currentStep++;
                        }
                    } else {
                        // Show validation errors in console for debugging
                        console.log('Validation errors:', this.validationErrors);
                    }
                } catch (error) {
                    console.error('Error in nextStep:', error);
                    alert('An error occurred. Please check the console for details.');
                } finally {
                    this.processing = false;
                }
            }, 50);
        },
        
        previousStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
                // Broadcast step change
                window.dispatchEvent(new CustomEvent('step-changed', { detail: { step: this.currentStep } }));
            }
        },
        
        async submitForm() {
            if (!this.validateStep()) {
                return;
            }
            
            // Prepare form data
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            formData.append('client_id', this.formData.client_id);
            formData.append('issue_date', this.formData.issue_date);
            formData.append('due_date', this.formData.due_date);
            formData.append('invoice_reference', this.formData.invoice_reference || '');
            formData.append('notes', this.formData.notes || '');
            formData.append('payment_method', this.formData.payment_method || '');
            formData.append('payment_details', this.formData.payment_details || '');
            formData.append('status', this.formData.status);
            
            // Add items
            this.formData.items.forEach((item, index) => {
                formData.append(`items[${index}][description]`, item.description);
                formData.append(`items[${index}][quantity]`, item.quantity);
                formData.append(`items[${index}][unit_price]`, item.unit_price);
                formData.append(`items[${index}][total_price]`, item.total_price);
            });
            
            // Submit form
            const form = document.querySelector('form');
            const submitButton = form.querySelector('button[type="submit"]');
            
            // Create a temporary form for submission
            const tempForm = document.createElement('form');
            tempForm.method = 'POST';
            tempForm.action = form.action;
            
            // Copy all form data
            for (let [key, value] of formData.entries()) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                tempForm.appendChild(input);
            }
            
            document.body.appendChild(tempForm);
            tempForm.submit();
        }
    }
}
</script>

