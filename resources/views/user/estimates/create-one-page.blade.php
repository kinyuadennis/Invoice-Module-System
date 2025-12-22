@extends('layouts.user')

@section('title', 'Create Estimate')

@section('content')
<div class="max-w-7xl mx-auto space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between bg-white rounded-lg shadow-sm p-4 border border-gray-200">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create New Estimate</h1>
            <p class="text-sm text-gray-600 mt-1">Fill in the details below to create your estimate</p>
        </div>
    </div>

    <!-- Use the same invoice builder component - it will work for estimates too -->
    <x-one-page-invoice-builder
        :clients="$clients"
        :services="$services"
        :company="$company"
        :nextInvoiceNumber="$nextEstimateNumber" />
</div>

@push('scripts')
<script>
    // Override form submission to use estimates route
    document.addEventListener('DOMContentLoaded', function() {
        // Change the form action to estimates
        const observer = new MutationObserver(function(mutations) {
            const form = document.querySelector('form[action*="invoices"]');
            if (form && !form.dataset.estimateConverted) {
                form.action = form.action.replace('/app/invoices', '/app/estimates');
                form.dataset.estimateConverted = 'true';
            }
        });
        
        observer.observe(document.body, { childList: true, subtree: true });
        
        // Also check immediately
        setTimeout(() => {
            const form = document.querySelector('form[action*="invoices"]');
            if (form) {
                form.action = form.action.replace('/app/invoices', '/app/estimates');
            }
        }, 100);
    });
</script>
@endpush
@endsection

