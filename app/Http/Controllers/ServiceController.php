<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Appointment;
use App\Models\Estimate;
use App\Models\WorkOrder;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    /**
     * Display a listing of the services.
     */
    public function index(Request $request)
    {
        $query = Service::query()->with(['customer', 'vehicle']);
        
        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        
        // Filter by vehicle
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        
        // Filter by stage
        if ($request->filled('stage')) {
            switch ($request->stage) {
                case 'appointment':
                    $query->whereHas('appointment');
                    break;
                case 'estimate':
                    $query->whereHas('estimate');
                    break;
                case 'work_order':
                    $query->whereHas('workOrder');
                    break;
                case 'invoice':
                    $query->whereHas('invoice');
                    break;
                case 'payment':
                    $query->whereHas('payments');
                    break;
            }
        }
        
        // Filter by payment status
        if ($request->filled('payment_status')) {
            if ($request->payment_status === 'paid') {
                $query->whereHas('payments', function($q) {
                    $q->select(DB::raw('SUM(amount) as total_paid'))
                      ->groupBy('service_id')
                      ->havingRaw('total_paid >= (SELECT total_amount FROM invoices WHERE service_id = payments.service_id LIMIT 1)');
                });
            } elseif ($request->payment_status === 'unpaid') {
                $query->whereDoesntHave('payments')
                      ->orWhereHas('payments', function($q) {
                          $q->select(DB::raw('SUM(amount) as total_paid'))
                            ->groupBy('service_id')
                            ->havingRaw('total_paid < (SELECT total_amount FROM invoices WHERE service_id = payments.service_id LIMIT 1)');
                      });
            }
        }
        
        // Search by service ID or customer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('service_id', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q2) use ($search) {
                      $q2->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('vehicle', function($q2) use ($search) {
                      $q2->where('license_plate', 'like', "%{$search}%")
                         ->orWhere('make', 'like', "%{$search}%")
                         ->orWhere('model', 'like', "%{$search}%");
                  });
            });
        }
        
        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        
        $services = $query->paginate(20);
        
        return view('services.index', compact('services'));
    }

    /**
     * Display the specified service.
     */
    public function show($serviceId)
    {
        $service = Service::with([
            'customer',
            'vehicle',
            'appointment',
            'estimate',
            'workOrder',
            'invoice',
            'payments'
        ])->findOrFail($serviceId);
        
        return view('services.show', compact('service'));
    }

    /**
     * Get service workflow timeline.
     */
    public function timeline($serviceId)
    {
        $service = Service::with([
            'appointment',
            'estimate',
            'workOrder',
            'invoice',
            'payments'
        ])->findOrFail($serviceId);
        
        $timeline = [];
        
        // Appointment
        if ($service->appointment) {
            $timeline[] = [
                'stage' => 'appointment',
                'title' => 'Appointment Scheduled',
                'description' => 'Service appointment was scheduled',
                'date' => $service->appointment->created_at,
                'status' => $service->appointment->appointment_status,
                'data' => $service->appointment,
            ];
        }
        
        // Estimate
        if ($service->estimate) {
            $timeline[] = [
                'stage' => 'estimate',
                'title' => 'Estimate Created',
                'description' => 'Service estimate was created',
                'date' => $service->estimate->created_at,
                'status' => $service->estimate->status,
                'data' => $service->estimate,
            ];
        }
        
        // Work Order
        if ($service->workOrder) {
            $timeline[] = [
                'stage' => 'work_order',
                'title' => 'Work Order Created',
                'description' => 'Work order was created for service',
                'date' => $service->workOrder->created_at,
                'status' => $service->workOrder->work_order_status,
                'data' => $service->workOrder,
            ];
        }
        
        // Invoice
        if ($service->invoice) {
            $timeline[] = [
                'stage' => 'invoice',
                'title' => 'Invoice Created',
                'description' => 'Invoice was generated',
                'date' => $service->invoice->created_at,
                'status' => $service->invoice->status,
                'data' => $service->invoice,
            ];
        }
        
        // Payments
        foreach ($service->payments as $payment) {
            $timeline[] = [
                'stage' => 'payment',
                'title' => 'Payment Received',
                'description' => 'Payment of ₱' . number_format($payment->amount, 2) . ' received',
                'date' => $payment->created_at,
                'status' => $payment->status,
                'data' => $payment,
            ];
        }
        
        // Sort by date
        usort($timeline, function($a, $b) {
            return $a['date'] <=> $b['date'];
        });
        
        return response()->json([
            'service_id' => $serviceId,
            'current_stage' => $service->current_stage,
            'workflow_progress' => $service->workflow_progress,
            'timeline' => $timeline,
        ]);
    }

    /**
     * Get service statistics.
     */
    public function statistics()
    {
        $totalServices = Service::count();
        $servicesWithAppointment = Service::has('appointment')->count();
        $servicesWithEstimate = Service::has('estimate')->count();
        $servicesWithWorkOrder = Service::has('workOrder')->count();
        $servicesWithInvoice = Service::has('invoice')->count();
        $servicesWithPayments = Service::has('payments')->count();
        
        $stageDistribution = [
            'appointment_scheduled' => Service::whereHas('appointment')->whereDoesntHave('estimate')->count(),
            'estimated' => Service::whereHas('estimate')->whereDoesntHave('workOrder')->count(),
            'work_in_progress' => Service::whereHas('workOrder')->whereDoesntHave('invoice')->count(),
            'invoiced' => Service::whereHas('invoice')->whereDoesntHave('payments')->count(),
            'payment_completed' => Service::whereHas('payments', function($q) {
                $q->select(DB::raw('SUM(amount) as total_paid'))
                  ->groupBy('service_id')
                  ->havingRaw('total_paid >= (SELECT total_amount FROM invoices WHERE service_id = payments.service_id LIMIT 1)');
            })->count(),
        ];
        
        $revenue = Payment::where('status', 'completed')->sum('amount');
        $outstanding = Invoice::sum('balance_due');
        
        return response()->json([
            'total_services' => $totalServices,
            'stage_distribution' => $stageDistribution,
            'revenue' => $revenue,
            'outstanding' => $outstanding,
            'completion_rate' => $totalServices > 0 ? ($stageDistribution['payment_completed'] / $totalServices * 100) : 0,
        ]);
    }

    /**
     * Create a new service workflow.
     */
    public function createWorkflow(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'service_type' => 'required|string',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Generate service ID
            $serviceId = Service::generateServiceId();
            
            // Create appointment
            $appointment = Appointment::create([
                'service_id' => $serviceId,
                'customer_id' => $request->customer_id,
                'vehicle_id' => $request->vehicle_id,
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
                'appointment_type' => $request->service_type,
                'appointment_status' => 'scheduled',
                'appointment_number' => Appointment::generateAppointmentNumber(),
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Service workflow created successfully',
                'service_id' => $serviceId,
                'appointment_id' => $appointment->id,
                'next_steps' => [
                    'create_estimate' => route('estimates.create', ['appointment_id' => $appointment->id]),
                    'view_service' => route('services.show', $serviceId),
                ],
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create service workflow: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Advance service to next stage.
     */
    public function advanceStage(Request $request, $serviceId)
    {
        $request->validate([
            'stage' => 'required|in:estimate,work_order,invoice,payment',
            'data' => 'required|array',
        ]);
        
        $service = Service::findOrFail($serviceId);
        
        DB::beginTransaction();
        
        try {
            switch ($request->stage) {
                case 'estimate':
                    // Create estimate
                    $estimate = Estimate::create(array_merge($request->data, [
                        'service_id' => $serviceId,
                        'appointment_id' => $service->appointment->id,
                        'estimate_number' => 'EST-' . date('Ymd') . '-' . str_pad(Estimate::count() + 1, 4, '0', STR_PAD_LEFT),
                        'status' => 'draft',
                    ]));
                    break;
                    
                case 'work_order':
                    // Create work order
                    $workOrder = WorkOrder::create(array_merge($request->data, [
                        'service_id' => $serviceId,
                        'appointment_id' => $service->appointment->id,
                        'estimate_id' => $service->estimate->id,
                        'work_order_number' => WorkOrder::generateWorkOrderNumber(),
                        'work_order_status' => 'draft',
                    ]));
                    break;
                    
                case 'invoice':
                    // Create invoice
                    $invoice = Invoice::create(array_merge($request->data, [
                        'service_id' => $serviceId,
                        'appointment_id' => $service->appointment->id,
                        'estimate_id' => $service->estimate->id,
                        'work_order_id' => $service->workOrder->id,
                        'invoice_number' => 'INV-' . date('Ymd') . '-' . str_pad(Invoice::count() + 1, 4, '0', STR_PAD_LEFT),
                        'status' => 'draft',
                    ]));
                    break;
                    
                case 'payment':
                    // Create payment
                    $payment = Payment::create(array_merge($request->data, [
                        'service_id' => $serviceId,
                        'invoice_id' => $service->invoice->id,
                        'status' => 'pending',
                    ]));
                    break;
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Service advanced to ' . $request->stage . ' stage',
                'current_stage' => $service->fresh()->current_stage,
                'workflow_progress' => $service->fresh()->workflow_progress,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to advance service stage: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get service by ID with all relationships.
     */
    public function getServiceDetails($serviceId)
    {
        $service = Service::with([
            'customer',
            'vehicle',
            'appointment',
            'estimate.items',
            'workOrder.items',
            'invoice.items',
            'payments'
        ])->findOrFail($serviceId);
        
        return response()->json([
            'service' => $service,
            'summary' => $service->getSummary(),
            'relationships' => [
                'appointment_id' => $service->appointment->id ?? null,
                'estimate_id' => $service->estimate->id ?? null,
                'work_order_id' => $service->workOrder->id ?? null,
                'invoice_id' => $service->invoice->id ?? null,
                'payment_ids' => $service->payments->pluck('id')->toArray(),
            ],
        ]);
    }
}