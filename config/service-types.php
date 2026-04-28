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
        'auto_mechanical' => 'AUTO-MECHANICAL',
        'auto_electrical' => 'AUTO-ELECTRICAL',
        'auto_electronics' => 'AUTO-ELECTRONICS',
        'auto_air_conditioning' => 'AUTO AIR-CONDITIONING',
        'body_repair_painting' => 'BODY REPAIR AND PAINTING',
        'auto_parts_sales' => 'AUTO PARTS SALES',
        'home_service_request' => 'HOME SERVICE REQUEST',
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
        'work_orders'  => 'single',      // Single select for primary type
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
        'auto_mechanical' => '⚙️',
        'auto_electrical' => '⚡',
        'auto_electronics' => '🔌',
        'auto_air_conditioning' => '❄️',
        'body_repair_painting' => '🎨',
        'auto_parts_sales' => '🔩',
        'home_service_request' => '🏠',
    ],
];
