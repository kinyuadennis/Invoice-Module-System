<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreProductRequest;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        return view('products.index', [
            'products' => Product::all()
        ]);
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(StoreProductRequest $request)
{
    Product::create($request->validated());
    return redirect()->route('products.index');
}

}
