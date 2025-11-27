<?php

namespace App\Http\Services;

use App\Models\InvoiceItem;

class InvoiceItemService
{
    public function createInvoiceItem(array $data)
    {
        return InvoiceItem::create($data);
    }

    public function updateInvoiceItem($id, array $data)
    {
        $item = InvoiceItem::findOrFail($id);
        $item->update($data);

        return $item;
    }

    public function deleteInvoiceItem($id)
    {
        $item = InvoiceItem::findOrFail($id);
        $item->delete();
    }
}
