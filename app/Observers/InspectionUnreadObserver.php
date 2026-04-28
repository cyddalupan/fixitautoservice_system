<?php

namespace App\Observers;

use App\Models\VehicleInspection;

class InspectionUnreadObserver
{
    public function updated(VehicleInspection $inspection): void
    {
        if ($inspection->wasChanged() && !$inspection->wasChanged('viewed_at') && $inspection->viewed_at !== null) {
            $inspection->update(['viewed_at' => null]);
        }
    }
}
