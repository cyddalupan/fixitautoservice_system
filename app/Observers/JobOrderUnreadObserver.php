<?php

namespace App\Observers;

use App\Models\WorkOrder;

class WorkOrderUnreadObserver
{
    public function updated(WorkOrder $workOrder): void
    {
        if ($workOrder->wasChanged() && !$workOrder->wasChanged('viewed_at') && $workOrder->viewed_at !== null) {
            $workOrder->update(['viewed_at' => null]);
        }
    }
}
