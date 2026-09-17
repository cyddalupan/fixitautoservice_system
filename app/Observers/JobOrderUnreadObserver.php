<?php

namespace App\Observers;

use App\Models\JobOrder;

class JobOrderUnreadObserver
{
    public function updated(JobOrder $jobOrder): void
    {
        if ($jobOrder->wasChanged() && !$jobOrder->wasChanged('viewed_at') && $jobOrder->viewed_at !== null) {
            $jobOrder->update(['viewed_at' => null]);
        }
    }
}
