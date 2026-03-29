<?php

namespace App\Observers;

use App\Models\ServiceProgress;

class ServiceProgressObserver
{
    /**
     * Handle the ServiceProgress "created" event.
     */
    public function created(ServiceProgress $serviceProgress): void
    {
        //
    }

    /**
     * Handle the ServiceProgress "updated" event.
     */
    public function updated(ServiceProgress $serviceProgress): void
    {
        //
    }

    /**
     * Handle the ServiceProgress "deleted" event.
     */
    public function deleted(ServiceProgress $serviceProgress): void
    {
        //
    }

    /**
     * Handle the ServiceProgress "restored" event.
     */
    public function restored(ServiceProgress $serviceProgress): void
    {
        //
    }

    /**
     * Handle the ServiceProgress "force deleted" event.
     */
    public function forceDeleted(ServiceProgress $serviceProgress): void
    {
        //
    }
}
