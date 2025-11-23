<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    // Show the create order form
    public function create()
    {
        $products = Product::all();
        $customers = Customer::all();

        return view('orders.create', compact('products', 'customers'));
    }

    // Save order + items
    public function store(Request $request)
    {
        $order = $this->orderService->createOrder($request);

        return redirect()->route('invoices.generate', $order->id);
    }
}
