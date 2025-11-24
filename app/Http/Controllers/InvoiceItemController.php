<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateInvoiceItemRequest;
use Illuminate\Http\Request;
use App\Http\Requests\StoreInvoiceItemRequest;
use App\Services\InvoiceItemService;

class InvoiceItemController extends Controller
{
    protected $invoiceItemService;

    public function __construct(InvoiceItemService $invoiceItemService)
    {
        $this->invoiceItemService = $invoiceItemService;
    }

    public function store(StoreInvoiceItemRequest $request)
    {
        $item = $this->invoiceItemService->createInvoiceItem($request->validated());
        return response()->json(['invoice_item' => $item, 'message' => 'Invoice item added successfully'], 201);
    }

    public function update(UpdateInvoiceItemRequest $request, $id)
    {
        $item = $this->invoiceItemService->updateInvoiceItem($id, $request->validated());
        return response()->json(['invoice_item' => $item, 'message' => 'Invoice item updated successfully']);
    }

    public function destroy($id)
    {
        $this->invoiceItemService->deleteInvoiceItem($id);
        return response()->json(['message' => 'Invoice item deleted successfully']);
    }
}
