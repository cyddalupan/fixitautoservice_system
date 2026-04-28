<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production
        if ($this->app->environment('production')) {
            \URL::forceScheme('https');
        }
        
        // Use Bootstrap 5 pagination templates
        \Illuminate\Pagination\Paginator::useBootstrapFive();
        
        // Register unread notification observers — reset viewed_at when records are updated
        \App\Models\Appointment::observe(\App\Observers\AppointmentUnreadObserver::class);
        \App\Models\VehicleInspection::observe(\App\Observers\InspectionUnreadObserver::class);
        \App\Models\WorkOrder::observe(\App\Observers\WorkOrderUnreadObserver::class);
        \App\Models\ServiceRecord::observe(\App\Observers\ServiceRecordUnreadObserver::class);
        \App\Models\Estimate::observe(\App\Observers\EstimateUnreadObserver::class);
        \App\Models\Invoice::observe(\App\Observers\InvoiceUnreadObserver::class);
    }
}
