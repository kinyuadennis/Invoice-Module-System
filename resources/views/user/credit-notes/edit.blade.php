@extends('layouts.user')

@section('title', 'Edit Credit Note')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Edit Credit Note</h1>
        <p class="mt-1 text-sm text-gray-600">Update credit note details</p>
    </div>

    <form method="POST" action="{{ route('user.credit-notes.update', $creditNote['id']) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Credit Note Details -->
        <x-card>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Credit Note Details</h2>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-select 
                        name="reason" 
                        label="Reason for Credit Note *"
                        :options="[
                            ['value' => 'refund', 'label' => 'Refund'],
                            ['value' => 'adjustment', 'label' => 'Adjustment'],
                            ['value' => 'error', 'label' => 'Error Correction'],
                            ['value' => 'cancellation', 'label' => 'Cancellation'],
                            ['value' => 'other', 'label' => 'Other'],
                        ]"
                        value="{{ old('reason', $creditNote['reason'] ?? 'other') }}"
                        required
                    />
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason Details</label>
                    <textarea 
                        name="reason_details" 
                        rows="3"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Provide details about why this credit note is being issued..."
                    >{{ old('reason_details', $creditNote['reason_details'] ?? '') }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea 
                        name="notes" 
                        rows="3"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Additional notes..."
                    >{{ old('notes', $creditNote['notes'] ?? '') }}</textarea>
                </div>
            </div>
        </x-card>

        <!-- Original Invoice Info (Read-only) -->
        <x-card>
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Original Invoice</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Invoice Number</p>
                    <p class="font-semibold text-gray-900">{{ $creditNote['invoice']['invoice_number'] }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Client</p>
                    <p class="font-semibold text-gray-900">{{ $creditNote['client']['name'] ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Total Credit</p>
                    <p class="font-semibold text-gray-900">KES {{ number_format($creditNote['total_credit'] ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Remaining</p>
                    <p class="font-semibold text-gray-900">KES {{ number_format($creditNote['remaining_credit'] ?? 0, 2) }}</p>
                </div>
            </div>
        </x-card>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('user.credit-notes.show', $creditNote['id']) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <x-button type="submit" variant="primary">Update Credit Note</x-button>
        </div>
    </form>
</div>
@endsection

