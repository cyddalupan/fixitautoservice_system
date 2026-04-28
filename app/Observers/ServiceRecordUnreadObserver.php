<?php

namespace App\Observers;

use App\Models\ServiceRecord;

class ServiceRecordUnreadObserver
{
    public function updated(ServiceRecord $record): void
    {
        if ($record->wasChanged() && !$record->wasChanged('viewed_at') && $record->viewed_at !== null) {
            $record->update(['viewed_at' => null]);
        }
    }
}
