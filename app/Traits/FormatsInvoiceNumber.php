<?php

namespace App\Traits;

trait FormatsInvoiceNumber
{
    protected function formatInvoiceNumber(int $id): string
    {
        return 'INV-' . str_pad((string) $id, 5, '0', STR_PAD_LEFT);
    }
}

