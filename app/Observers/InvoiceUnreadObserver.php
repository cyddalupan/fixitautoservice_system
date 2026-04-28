<?php

namespace App\Observers;

use App\Models\Invoice;

class InvoiceUnreadObserver
{
    public function updated(Invoice $invoice): void
    {
        if ($invoice->wasChanged() && !$invoice->wasChanged('viewed_at') && $invoice->viewed_at !== null) {
            $invoice->update(['viewed_at' => null]);
        }
    }
}
