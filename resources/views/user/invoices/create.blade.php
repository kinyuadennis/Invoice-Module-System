@extends('layouts.user')

@section('title', 'Create Invoice')

@section('content')
    <x-invoice-wizard :clients="$clients" :services="$services" />
@endsection

