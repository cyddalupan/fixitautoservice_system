<?php

namespace App\Observers;

use App\Models\Estimate;

class EstimateUnreadObserver
{
    public function updated(Estimate $estimate): void
    {
        if ($estimate->wasChanged() && !$estimate->wasChanged('viewed_at') && $estimate->viewed_at !== null) {
            $estimate->update(['viewed_at' => null]);
        }
    }
}
