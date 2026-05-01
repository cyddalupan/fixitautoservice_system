<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class TransactionHistoryService
{
    /**
     * Types of transactions to include.
     */
    const TYPES = ['appointment', 'inspection', 'work_order', 'estimate', 'invoice', 'archived_inspection'];

    /**
     * Get unified transaction history for a customer.
     * Merges data from all transaction tables + archives, sorted by date desc.
     */
    public static function forCustomer(int $customerId): array
    {
        $transactions = [];

        // --- Appointments ---
        $appts = DB::table('appointments')
            ->where('customer_id', $customerId)
            ->select(
                DB::raw("'appointment' as type"),
                'id',
                'appointment_type as service_type',
                'appointment_status as status',
                'service_request as description',
                'vehicle_id',
                'created_at',
                'updated_at',
                DB::raw("NULL as total_amount"),
                DB::raw("NULL as archived_at")
            )
            ->get();

        foreach ($appts as $a) {
            $vehicle = DB::table('vehicles')->find($a->vehicle_id);
            $a->vehicle_label = $vehicle ? "{$vehicle->make} {$vehicle->model}" : "Vehicle #{$a->vehicle_id}";
            $a->url = route('appointments.show', $a->id, false);
            $a->ref_number = $a->type === 'appointment' ? 'APT-' . str_pad($a->id, 5, '0', STR_PAD_LEFT) : null;
            $transactions[] = $a;
        }

        // --- Inspections ---
        $vehicles = DB::table('vehicles')->where('customer_id', $customerId)->pluck('id');
        if ($vehicles->isNotEmpty()) {
            $inspections = DB::table('vehicle_inspections')
                ->whereIn('vehicle_id', $vehicles)
                ->select(
                    DB::raw("'inspection' as type"),
                    'id',
                    'service_type',
                    'inspection_status as status',
                    'customer_concerns as description',
                    'vehicle_id',
                    'created_at',
                    'updated_at',
                    DB::raw("NULL as total_amount"),
                    DB::raw("NULL as archived_at")
                )
                ->get();

            foreach ($inspections as $i) {
                $vehicle = DB::table('vehicles')->find($i->vehicle_id);
                $i->vehicle_label = $vehicle ? "{$vehicle->make} {$vehicle->model}" : "Vehicle #{$i->vehicle_id}";
                $i->url = route('inspections.show', $i->id, false);
                $i->ref_number = 'INS-' . str_pad($i->id, 5, '0', STR_PAD_LEFT);
                $transactions[] = $i;
            }

            // --- Archived Inspections ---
            // Try direct customer_id match first
            $archives = DB::table('archives')
                ->where('source_module', 'inspection')
                ->where(function ($q) use ($customerId, $vehicles) {
                    $q->whereRaw('JSON_EXTRACT(original_data, "$.customer_id") = ?', [$customerId]);
                    foreach ($vehicles as $vid) {
                        $q->orWhereRaw('JSON_EXTRACT(original_data, "$.vehicle_id") = ?', [$vid]);
                    }
                })
                ->orderBy('archived_at', 'desc')
                ->get();

            foreach ($archives as $ar) {
                $od = is_string($ar->original_data) ? json_decode($ar->original_data, true) : $ar->original_data;
                if (!$od) continue;

                $vehicleId = $od['vehicle_id'] ?? null;
                $vehicleMake = $od['vehicle_make'] ?? '';
                $vehicleModel = $od['vehicle_model'] ?? '';
                $vLabel = $vehicleMake ? "$vehicleMake $vehicleModel" : ($vehicleId ? "Vehicle #$vehicleId" : 'Unknown');

                $t = (object)[
                    'type' => 'archived_inspection',
                    'id' => $ar->archivable_id,
                    'archive_id' => $ar->id,
                    'service_type' => $od['inspection_type'] ?? $od['service_type'] ?? 'N/A',
                    'status' => 'archived',
                    'description' => $od['customer_concerns'] ?? '—',
                    'vehicle_id' => $vehicleId,
                    'vehicle_label' => $vLabel,
                    'created_at' => $od['created_at'] ?? $ar->archived_at,
                    'updated_at' => $ar->archived_at,
                    'archived_at' => $ar->archived_at,
                    'total_amount' => null,
                    'url' => null,
                    'ref_number' => 'INS-' . str_pad($od['id'] ?? $ar->archivable_id, 5, '0', STR_PAD_LEFT),
                ];
                $transactions[] = $t;
            }
        }

        // --- Work Orders ---
        $wos = DB::table('work_orders')
            ->where('customer_id', $customerId)
            ->select(
                DB::raw("'work_order' as type"),
                'id',
                'service_type',
                'work_order_status as status',
                'customer_concerns as description',
                'vehicle_id',
                'created_at',
                'updated_at',
                DB::raw("NULL as total_amount"),
                DB::raw("NULL as archived_at")
            )
            ->get();

        foreach ($wos as $wo) {
            $vehicle = DB::table('vehicles')->find($wo->vehicle_id);
            $wo->vehicle_label = $vehicle ? "{$vehicle->make} {$vehicle->model}" : "Vehicle #{$wo->vehicle_id}";
            $wo->url = route('work-orders.show', $wo->id, false);
            $wo->ref_number = 'WO-' . str_pad($wo->id, 5, '0', STR_PAD_LEFT);
            $transactions[] = $wo;
        }

        // --- Estimates ---
        $ests = DB::table('estimates')
            ->where('customer_id', $customerId)
            ->select(
                DB::raw("'estimate' as type"),
                'id',
                'service_type',
                'status',
                DB::raw("customer_notes as description"),
                'vehicle_id',
                'created_at',
                'updated_at',
                'total_amount',
                DB::raw("NULL as archived_at")
            )
            ->get();

        foreach ($ests as $e) {
            $vehicle = DB::table('vehicles')->find($e->vehicle_id);
            $e->vehicle_label = $vehicle ? "{$vehicle->make} {$vehicle->model}" : "Vehicle #{$e->vehicle_id}";
            $e->url = route('estimates.show', $e->id, false);
            $e->ref_number = 'EST-' . str_pad($e->id, 5, '0', STR_PAD_LEFT);
            $transactions[] = $e;
        }

        // --- Invoices ---
        $invs = DB::table('invoices')
            ->where('customer_id', $customerId)
            ->select(
                DB::raw("'invoice' as type"),
                'id',
                DB::raw("NULL as service_type"),
                'status',
                DB::raw("NULL as description"),
                'vehicle_id',
                'created_at',
                'updated_at',
                'total_amount',
                DB::raw("NULL as archived_at")
            )
            ->get();

        foreach ($invs as $inv) {
            $vehicle = DB::table('vehicles')->find($inv->vehicle_id);
            $inv->vehicle_label = $vehicle ? "{$vehicle->make} {$vehicle->model}" : "Vehicle #{$inv->vehicle_id}";
            $inv->url = route('invoices.show', $inv->id, false);
            $inv->ref_number = 'INV-' . str_pad($inv->id, 5, '0', STR_PAD_LEFT);
            $transactions[] = $inv;
        }

        // Sort by created_at descending (with fallback)
        usort($transactions, function ($a, $b) {
            $ta = $a->created_at ?? $a->archived_at ?? '2000-01-01';
            $tb = $b->created_at ?? $b->archived_at ?? '2000-01-01';
            return strtotime($tb) - strtotime($ta);
        });

        return $transactions;
    }

    /**
     * Get unified transaction history for a vehicle.
     */
    public static function forVehicle(int $vehicleId): array
    {
        $customerId = DB::table('vehicles')->where('id', $vehicleId)->value('customer_id');
        return self::forCustomer($customerId);
    }

    /**
     * Get status badge color.
     */
    public static function statusColor(string $type, string $status): string
    {
        // Normalize
        $s = strtolower(trim($status));

        $colorMap = [
            // Completed/positive states
            'completed' => 'bg-success',
            'paid' => 'bg-success',
            'released' => 'bg-success',
            'won' => 'bg-success',
            'converted_to_customer' => 'bg-success',
            'confirmed' => 'bg-success',

            // In progress
            'in_progress' => 'bg-warning text-dark',
            'checked_in' => 'bg-warning text-dark',
            'repairing' => 'bg-warning text-dark',
            'pending' => 'bg-warning text-dark',
            'scheduled' => 'bg-info',
            'draft' => 'bg-secondary',
            'sent' => 'bg-info',
            'viewed' => 'bg-info',
            'partial' => 'bg-info',

            // Negative/cancelled
            'cancelled' => 'bg-danger',
            'no_show' => 'bg-danger',
            'lost' => 'bg-danger',
            'archived' => 'bg-secondary',
            'overdue' => 'bg-danger',
            'refunded' => 'bg-danger',

            // Waiting
            'waiting_parts' => 'bg-info',
            'rescheduled' => 'bg-info',
        ];

        return $colorMap[$s] ?? 'bg-secondary';
    }
}
