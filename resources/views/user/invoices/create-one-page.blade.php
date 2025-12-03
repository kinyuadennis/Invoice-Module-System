@extends('layouts.user')

@section('title', 'Create Invoice')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-one-page-invoice-builder 
        :clients="$clients" 
        :services="$services" 
        :company="$company"
        :nextInvoiceNumber="$nextInvoiceNumber"
    />
</div>
@endsection

