<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Master Service Types List
    |--------------------------------------------------------------------------
    |
    | These are the official service types used across the entire platform.
    | All modules (appointments, inspections, work orders, estimates,
    | service records) pull from this single source of truth.
    |
    */
    'list' => [
        'preventive_maintenance' => 'PREVENTIVE MAINTENANCE',
        'basic_tune_up' => 'BASIC TUNE UP',
        'egr_service' => 'EGR SERVICE',
        'aircon_cleaning' => 'AIRCON CLEANING',
        'aircon_general_cleaning' => 'AIRCON GENERAL CLEANING',
        'aircon_service' => 'AIRCON SERVICE',
        'underchassis_service' => 'UNDERCHASSIS SERVICE',
        'engine_service' => 'ENGINE SERVICE',
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Configuration
    |--------------------------------------------------------------------------
    |
    | Controls whether each module allows single or multiple selections.
    |
    */
    'module_config' => [
        'appointments' => 'single',      // Single select
        'inspections'  => 'single',      // Single select
        'job_orders'  => 'single',      // Single select for primary type
        'estimates'    => 'single',      // Single select
        'service_records' => 'single',   // Single select
    ],

    /*
    |--------------------------------------------------------------------------
    | Icons / Badges (optional, for UI rendering)
    |--------------------------------------------------------------------------
    */
    'icons' => [
        'preventive_maintenance' => '🔧',
        'basic_tune_up' => '⚙️',
        'egr_service' => '🔄',
        'aircon_cleaning' => '❄️',
        'aircon_general_cleaning' => '❄️',
        'aircon_service' => '❄️',
        'underchassis_service' => '🔩',
        'engine_service' => '🔧',
    ],
];
