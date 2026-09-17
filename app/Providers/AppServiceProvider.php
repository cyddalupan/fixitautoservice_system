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
        // Brevo REST API mail transport (same proven path as the public contact form)
        \Illuminate\Support\Facades\Mail::extend('brevo-api', function (array $config) {
            return new \App\Mail\Transports\BrevoApiTransport(
                $config['api_key'] ?? env('BREVO_API_KEY', ''),
                $config['sender_email'] ?? env('BREVO_SENDER_EMAIL', env('MAIL_FROM_ADDRESS', 'noreply@fixitautoservices.com')),
                $config['sender_name'] ?? env('BREVO_SENDER_NAME', env('MAIL_FROM_NAME', 'Fix-It Auto Services Center')),
            );
        });

        // Force HTTPS in production
        if ($this->app->environment('production')) {
            \URL::forceScheme('https');
        }

        // ===================================================================
        // Production safety guard: block destructive / data-seeding commands.
        //
        // Destructive and seeding commands are blocked when running from the
        // console in PRODUCTION (where they would hit the real database). Unit
        // tests are exempt because they run under APP_ENV=testing against an
        // isolated database and never touch the production DB. To deliberately
        // run one of these in production (emergency), pass --force.
        // ===================================================================
        if (
            $this->app->runningInConsole()
            && !$this->app->runningUnitTests()
            && $this->app->environment('production')
        ) {
            $blockedCommands = [
                'db:seed',
                'db:wipe',
                'migrate:fresh',
                'migrate:refresh',
                'migrate:reset',
                'inventory:generate-sample-data',
                'suppliers:generate-sample-data',
                'hr-payroll:generate-sample-data',
                // class-name aliases (in case they're invoked by class)
                'GenerateInventorySampleData',
                'GenerateSupplierSampleData',
                'GenerateHrPayrollSampleData',
            ];

            \Illuminate\Support\Facades\Event::listen(
                \Illuminate\Console\Events\CommandStarting::class,
                function (\Illuminate\Console\Events\CommandStarting $event) use ($blockedCommands) {
                    $name = $event->command;
                    // Allow non-blocked commands, or when an explicit --force was passed.
                    if (!in_array($name, $blockedCommands, true)) {
                        return;
                    }
                    $input = $event->input;
                    $forced = $input->hasOption('force') && (bool) $input->getOption('force');
                    if ($forced) {
                        return;
                    }
                    $event->input->setInteractive(false);
                    throw new \RuntimeException(
                        "[PRODUCTION GUARD] '{$name}' is blocked because it can destroy or seed data " .
                        'in the production database. If this is intentional, re-run it with --force.'
                    );
                }
            );
        }

        // Use Bootstrap 5 pagination templates
        \Illuminate\Pagination\Paginator::useBootstrapFive();
        
        // Register unread notification observers — reset viewed_at when records are updated
        \App\Models\Appointment::observe(\App\Observers\AppointmentUnreadObserver::class);
        \App\Models\VehicleInspection::observe(\App\Observers\InspectionUnreadObserver::class);
        \App\Models\JobOrder::observe(\App\Observers\JobOrderUnreadObserver::class);
        \App\Models\ServiceRecord::observe(\App\Observers\ServiceRecordUnreadObserver::class);
        \App\Models\Estimate::observe(\App\Observers\EstimateUnreadObserver::class);
        \App\Models\Invoice::observe(\App\Observers\InvoiceUnreadObserver::class);
    }
}
