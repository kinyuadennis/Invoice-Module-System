@extends('layouts.user')

@section('title', 'Create Invoice')

@section('content')
<div class="max-w-7xl mx-auto space-y-4">
    <!-- Header with Customize Link -->
    <div class="flex items-center justify-between bg-white rounded-lg shadow-sm p-4 border border-gray-200">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create New Invoice</h1>
            <p class="text-sm text-gray-600 mt-1">Fill in the details below to create your invoice</p>
        </div>
        <a href="{{ route('user.company.invoice-customization') }}" class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
            </svg>
            <span>Customize Invoice Settings</span>
        </a>
    </div>


    <x-one-page-invoice-builder
        :clients="$clients"
        :services="$services"
        :company="$company"
        :nextInvoiceNumber="$nextInvoiceNumber" />
</div>
@endsection