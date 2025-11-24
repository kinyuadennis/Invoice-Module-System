<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreCustomerRequest;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        return view('customers.index', [
            'customers' => Customer::all()
        ]);
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        Customer::create($request->validated());
        return back()->with('success', 'Customer added.');
    }
    
}
