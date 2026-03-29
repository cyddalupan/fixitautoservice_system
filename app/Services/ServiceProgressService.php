<?php

namespace App\Services;

use App\Models\ServiceProgress;
use App\Models\Appointment;
use App\Models\Inspection;
use App\Models\Estimate;
use App\Models\WorkOrder;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;

class ServiceProgressService
{
    /**
     * Create or update service progress when an appointment is created.
     */
    public static function updateFromAppointment(Appointment $appointment): ServiceProgress
    {
        $progress = ServiceProgress::firstOrCreate(
            [
                'serviceable_id' => $appointment->id,
                'serviceable_type' => Appointment::class,
            ],
            [
                'customer_id' => $appointment->customer_id,
                'vehicle_id' => $appointment->vehicle_id,
                'service_type' => 'full_service',
                'started_at' => now(),
            ]
        );

        $progress->update([
            'has_appointment' => true,
            'appointment_id' => $appointment->id,
            'appointment_created_at' => $appointment->created_at,
        ]);

        $progress->progress_percentage = $progress->calculateProgressPercentage();
        $progress->updateCurrentStage();
        
        if ($progress->progress_percentage === 100) {
            $progress->completed_at = now();
            $progress->save();
        }

        return $progress;
    }

    /**
     * Update service progress when an inspection is created.
     */
    public static function updateFromInspection(Inspection $inspection): ServiceProgress
    {
        // Find related appointment
        $appointment = $inspection->appointment;
        
        if (!$appointment) {
            // Try to find progress by customer/vehicle
            $progress = ServiceProgress::where('customer_id', $inspection->customer_id)
                ->where('vehicle_id', $inspection->vehicle_id)
                ->where('service_type', 'full_service')
                ->latest()
                ->first();
                
            if (!$progress) {
                // Create new progress
                $progress = ServiceProgress::create([
                    'customer_id' => $inspection->customer_id,
                    'vehicle_id' => $inspection->vehicle_id,
                    'service_type' => 'full_service',
                    'started_at' => now(),
                ]);
            }
        } else {
            // Find progress by appointment
            $progress = ServiceProgress::where('appointment_id', $appointment->id)->first();
            
            if (!$progress) {
                $progress = self::updateFromAppointment($appointment);
            }
        }

        $progress->update([
            'has_inspection' => true,
            'inspection_id' => $inspection->id,
            'inspection_created_at' => $inspection->created_at,
        ]);

        $progress->progress_percentage = $progress->calculateProgressPercentage();
        $progress->updateCurrentStage();

        return $progress;
    }

    /**
     * Update service progress when an estimate is created.
     */
    public static function updateFromEstimate(Estimate $estimate): ServiceProgress
    {
        // Try to find by appointment first
        $appointment = $estimate->appointment;
        $progress = null;
        
        if ($appointment) {
            $progress = ServiceProgress::where('appointment_id', $appointment->id)->first();
        }
        
        // If not found by appointment, try by customer/vehicle
        if (!$progress) {
            $progress = ServiceProgress::where('customer_id', $estimate->customer_id)
                ->where('vehicle_id', $estimate->vehicle_id)
                ->where('service_type', 'full_service')
                ->latest()
                ->first();
        }
        
        // If still not found, create new
        if (!$progress) {
            $progress = ServiceProgress::create([
                'customer_id' => $estimate->customer_id,
                'vehicle_id' => $estimate->vehicle_id,
                'service_type' => 'full_service',
                'started_at' => now(),
            ]);
        }

        $progress->update([
            'has_estimate' => true,
            'estimate_id' => $estimate->id,
            'estimate_created_at' => $estimate->created_at,
        ]);

        $progress->progress_percentage = $progress->calculateProgressPercentage();
        $progress->updateCurrentStage();

        return $progress;
    }

    /**
     * Update service progress when a work order is created.
     */
    public static function updateFromWorkOrder(WorkOrder $workOrder): ServiceProgress
    {
        // Try to find by estimate first
        $estimate = $workOrder->estimate;
        $progress = null;
        
        if ($estimate) {
            $progress = ServiceProgress::where('estimate_id', $estimate->id)->first();
        }
        
        // If not found by estimate, try by customer/vehicle
        if (!$progress) {
            $progress = ServiceProgress::where('customer_id', $workOrder->customer_id)
                ->where('vehicle_id', $workOrder->vehicle_id)
                ->where('service_type', 'full_service')
                ->latest()
                ->first();
        }
        
        // If still not found, create new
        if (!$progress) {
            $progress = ServiceProgress::create([
                'customer_id' => $workOrder->customer_id,
                'vehicle_id' => $workOrder->vehicle_id,
                'service_type' => 'full_service',
                'started_at' => now(),
            ]);
        }

        $progress->update([
            'has_work_order' => true,
            'work_order_id' => $workOrder->id,
            'work_order_created_at' => $workOrder->created_at,
        ]);

        $progress->progress_percentage = $progress->calculateProgressPercentage();
        $progress->updateCurrentStage();

        return $progress;
    }

    /**
     * Update service progress when an invoice is created.
     * This handles both full service and parts purchase.
     */
    public static function updateFromInvoice(Invoice $invoice): ServiceProgress
    {
        // Determine service type
        $serviceType = $invoice->invoice_type === 'parts' ? 'parts_purchase' : 'full_service';
        
        // Try to find by work order first (for full service)
        if ($serviceType === 'full_service') {
            $workOrder = $invoice->workOrder;
            if ($workOrder) {
                $progress = ServiceProgress::where('work_order_id', $workOrder->id)->first();
                if ($progress) {
                    $progress->update([
                        'has_invoice' => true,
                        'invoice_id' => $invoice->id,
                        'invoice_created_at' => $invoice->created_at,
                    ]);
                    
                    $progress->progress_percentage = $progress->calculateProgressPercentage();
                    $progress->updateCurrentStage();
                    return $progress;
                }
            }
        }
        
        // For parts purchase or if not found by work order
        $progress = ServiceProgress::firstOrCreate(
            [
                'serviceable_id' => $invoice->id,
                'serviceable_type' => Invoice::class,
            ],
            [
                'customer_id' => $invoice->customer_id,
                'vehicle_id' => $invoice->vehicle_id,
                'service_type' => $serviceType,
                'started_at' => now(),
            ]
        );

        $progress->update([
            'has_invoice' => true,
            'invoice_id' => $invoice->id,
            'invoice_created_at' => $invoice->created_at,
        ]);

        $progress->progress_percentage = $progress->calculateProgressPercentage();
        $progress->updateCurrentStage();

        return $progress;
    }

    /**
     * Update service progress when a payment is created.
     */
    public static function updateFromPayment(Payment $payment): ServiceProgress
    {
        // Try to find by invoice first
        $invoice = $payment->invoice;
        $progress = null;
        
        if ($invoice) {
            $progress = ServiceProgress::where('invoice_id', $invoice->id)->first();
        }
        
        // If not found by invoice, try by customer
        if (!$progress) {
            $progress = ServiceProgress::where('customer_id', $payment->customer_id)
                ->latest()
                ->first();
        }
        
        // If still not found, create new (for direct payments)
        if (!$progress) {
            $progress = ServiceProgress::create([
                'customer_id' => $payment->customer_id,
                'service_type' => 'parts_purchase',
                'started_at' => now(),
            ]);
        }

        $progress->update([
            'has_payment' => true,
            'payment_id' => $payment->id,
            'payment_created_at' => $payment->created_at,
        ]);

        $progress->progress_percentage = $progress->calculateProgressPercentage();
        $progress->updateCurrentStage();
        
        if ($progress->progress_percentage === 100) {
            $progress->completed_at = now();
            $progress->save();
        }

        return $progress;
    }

    /**
     * Get progress bar HTML for a service entity.
     */
    public static function getProgressBarForEntity(Model $entity): ?string
    {
        $progress = null;
        
        if ($entity instanceof Appointment) {
            $progress = ServiceProgress::where('appointment_id', $entity->id)->first();
        } elseif ($entity instanceof Inspection) {
            $progress = ServiceProgress::where('inspection_id', $entity->id)->first();
        } elseif ($entity instanceof Estimate) {
            $progress = ServiceProgress::where('estimate_id', $entity->id)->first();
        } elseif ($entity instanceof WorkOrder) {
            $progress = ServiceProgress::where('work_order_id', $entity->id)->first();
        } elseif ($entity instanceof Invoice) {
            $progress = ServiceProgress::where('invoice_id', $entity->id)->first();
        } elseif ($entity instanceof Payment) {
            $progress = ServiceProgress::where('payment_id', $entity->id)->first();
        }
        
        if ($progress) {
            return $progress->getProgressBarHtml();
        }
        
        return null;
    }
}