@extends('layouts.user')

@section('title', 'Create Invoice')

@section('content')
    <x-invoice-wizard :clients="$clients" :services="$services" :company="$company" :nextInvoiceNumber="$nextInvoiceNumber ?? null" />
@endsection

