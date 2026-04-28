<?php

namespace App\Services;

use App\Models\Archive;
use App\Models\WorkOrder;
use App\Models\Estimate;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class ArchiveService
{
    /**
     * Archive a record: save its data to the archives table,
     * then soft-delete the original.
     *
     * @param WorkOrder|Estimate|Payment|Invoice $record
     * @param string $sourceModule  'work_order', 'estimate', 'payment', 'invoice'
     * @return Archive
     */
    public static function archive($record, string $sourceModule): Archive
    {
        $data = $record->toArray();

        // Enrich with customer/vehicle info for easy search
        $data = self::enrichWithRelations($record, $data, $sourceModule);

        return DB::transaction(function () use ($record, $data, $sourceModule) {
            // Create archive entry FIRST
            $archive = Archive::create([
                'archivable_id' => $record->id,
                'archivable_type' => get_class($record),
                'source_module' => $sourceModule,
                'archived_by' => auth()->id(),
                'original_data' => $data,
                'notes' => null,
                'archived_at' => now(),
            ]);

            // Then soft-delete the original record
            $record->delete();

            return $archive;
        });
    }

    /**
     * Enrich raw record data with customer/vehicle info for searchability.
     */
    private static function enrichWithRelations($record, array $data, string $module): array
    {
        // Try to get customer info
        $customer = null;
        $vehicle = null;

        // If the record itself is a Customer, use it directly
        if ($record instanceof \App\Models\Customer) {
            $customer = $record;
        } elseif (method_exists($record, 'customer')) {
            try { $customer = $record->customer; } catch (\Exception $e) {}
        }

        if (method_exists($record, 'vehicle')) {
            try { $vehicle = $record->vehicle; } catch (\Exception $e) {}
        }

        // Fallback by direct customer_id / vehicle_id
        if (!$customer && !empty($data['customer_id'])) {
            $customer = Customer::find($data['customer_id']);
        }
        if (!$vehicle && !empty($data['vehicle_id'])) {
            $vehicle = Vehicle::find($data['vehicle_id']);
        }

        if ($customer) {
            $data['customer_name'] = trim($customer->first_name . ' ' . $customer->last_name);
            $data['customer_first_name'] = $customer->first_name ?? '';
            $data['customer_last_name'] = $customer->last_name ?? '';
            $data['customer_email'] = $customer->email ?? '';
            $data['customer_phone'] = $customer->phone ?? '';
            $data['customer_city'] = $customer->city ?? '';
        }

        if ($vehicle) {
            $data['plate_number'] = $vehicle->plate_number ?? '';
            $data['vehicle_make'] = $vehicle->make ?? '';
            $data['vehicle_model'] = $vehicle->model ?? '';
            $data['vehicle_year'] = $vehicle->year ?? '';
        }

        // For customer module, ensure identifier is populated from name/id
        if ($module === 'customer' && empty($data['work_order_number']) && empty($data['estimate_number']) && empty($data['invoice_number']) && empty($data['reference_number'])) {
            $data['customer_id_for_display'] = $data['id'] ?? $record->id;
        }

        return $data;
    }
}
