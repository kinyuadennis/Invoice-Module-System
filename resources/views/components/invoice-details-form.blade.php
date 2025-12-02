@props(['issueDate' => null, 'dueDate' => null, 'reference' => null, 'notes' => null, 'company' => null])

<div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <!-- Issue Date -->
        <div>
            <label class="block text-sm font-semibold text-slate-900 mb-2">Issue Date *</label>
            <input 
                type="date" 
                x-model="formData.issue_date"
                @input="$dispatch('details-changed', formData)"
                :value="formData.issue_date || '{{ $issueDate ?? date('Y-m-d') }}'"
                :required="currentStep === 2"
                class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
            >
            <p x-show="validationErrors.issue_date" class="mt-1 text-sm text-red-600" x-text="validationErrors.issue_date"></p>
        </div>

        <!-- Due Date -->
        <div>
            <label class="block text-sm font-semibold text-slate-900 mb-2">Due Date *</label>
            <input 
                type="date" 
                x-model="formData.due_date"
                @input="$dispatch('details-changed', formData)"
                :value="formData.due_date || '{{ $dueDate }}'"
                :min="formData.issue_date"
                :required="currentStep === 2"
                class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
            >
            <p x-show="validationErrors.due_date" class="mt-1 text-sm text-red-600" x-text="validationErrors.due_date"></p>
        </div>
    </div>

    <!-- Invoice Reference -->
    <div>
        <label class="block text-sm font-semibold text-slate-900 mb-2">
            Invoice Reference
            <span class="text-xs font-normal text-slate-500">(Auto-generated, editable)</span>
        </label>
        <input 
            type="text" 
            x-model="formData.invoice_reference"
            @input="$dispatch('details-changed', formData)"
            :value="formData.invoice_reference || '{{ $reference ?? '' }}'"
            placeholder="INV-2024-0001"
            class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
        >
        <p class="mt-1 text-xs text-slate-500">
            @if($company)
                Format: {{ $company->invoice_prefix ?? 'INV' }}-{{ str_pad('1', $company->invoice_padding ?? 4, '0', STR_PAD_LEFT) }}
                @if($company->invoice_suffix)
                    -{{ $company->invoice_suffix }}
                @endif
                (Leave blank to auto-generate)
            @else
                Leave blank to auto-generate
            @endif
        </p>
        <p x-show="validationErrors.invoice_reference" class="mt-1 text-sm text-red-600" x-text="validationErrors.invoice_reference"></p>
    </div>

    <!-- Notes -->
    <div>
        <label class="block text-sm font-semibold text-slate-900 mb-2">Notes</label>
        <textarea 
            x-model="formData.notes"
            @input="$dispatch('details-changed', formData)"
            rows="4"
            class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
            placeholder="Any additional notes or terms..."
        >{{ $notes ?? '' }}</textarea>
        <p x-show="validationErrors.notes" class="mt-1 text-sm text-red-600" x-text="validationErrors.notes"></p>
    </div>
</div>

