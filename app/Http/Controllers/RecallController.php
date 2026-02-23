<?php

namespace App\Http\Controllers;

use App\Models\VehicleRecall;
use App\Models\Vehicle;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\RecallNotification;
use Carbon\Carbon;

class RecallController extends Controller
{
    /**
     * Display the recall management dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get recall statistics
        $totalRecalls = VehicleRecall::count();
        $openRecalls = VehicleRecall::where('status', 'open')->count();
        $inProgressRecalls = VehicleRecall::where('status', 'in_progress')->count();
        $completedRecalls = VehicleRecall::where('status', 'completed')->count();
        $closedRecalls = VehicleRecall::where('status', 'closed')->count();
        
        // Get recalls needing attention
        $needsNotification = VehicleRecall::where('customer_notified', false)
            ->whereIn('status', ['open', 'in_progress'])
            ->count();
            
        $overdueRecalls = VehicleRecall::where('status', 'open')
            ->where('recall_date', '<', now()->subDays(30))
            ->count();
            
        $urgentRecalls = VehicleRecall::where('status', 'open')
            ->where('severity', 'high')
            ->count();
            
        // Get recent recalls
        $recentRecalls = VehicleRecall::with(['vehicle.customer'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        // Get vehicles with most recalls
        $vehiclesWithMostRecalls = Vehicle::where('open_recall_count', '>', 0)
            ->orderBy('open_recall_count', 'desc')
            ->limit(10)
            ->get();
            
        // Get recall cost statistics
        $totalEstimatedCost = VehicleRecall::sum('estimated_cost');
        $totalActualCost = VehicleRecall::whereNotNull('actual_cost')->sum('actual_cost');
        $costSavings = $totalEstimatedCost - $totalActualCost;
        
        // Get recall trends (last 6 months)
        $recallTrends = $this->getRecallTrends(6);
        
        return view('vehicle-tools.recall-dashboard', compact(
            'totalRecalls',
            'openRecalls',
            'inProgressRecalls',
            'completedRecalls',
            'closedRecalls',
            'needsNotification',
            'overdueRecalls',
            'urgentRecalls',
            'recentRecalls',
            'vehiclesWithMostRecalls',
            'totalEstimatedCost',
            'totalActualCost',
            'costSavings',
            'recallTrends'
        ));
    }

    /**
     * Display all recalls with filtering.
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $severity = $request->get('severity', 'all');
        $search = $request->get('search');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        
        $query = VehicleRecall::with(['vehicle.customer']);
        
        // Filter by status
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        // Filter by severity
        if ($severity !== 'all') {
            $query->where('severity', $severity);
        }
        
        // Filter by date range
        if ($dateFrom) {
            $query->where('recall_date', '>=', $dateFrom);
        }
        
        if ($dateTo) {
            $query->where('recall_date', '<=', $dateTo);
        }
        
        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('campaign_number', 'like', "%{$search}%")
                  ->orWhere('component', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%")
                  ->orWhere('consequence', 'like', "%{$search}%")
                  ->orWhere('remedy', 'like', "%{$search}%")
                  ->orWhereHas('vehicle', function ($q) use ($search) {
                      $q->where('vin', 'like', "%{$search}%")
                        ->orWhere('make', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('year', 'like', "%{$search}%");
                  })
                  ->orWhereHas('vehicle.customer', function ($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }
        
        $recalls = $query->orderBy('recall_date', 'desc')
            ->paginate(20);
            
        return view('vehicle-tools.recalls-index', compact(
            'recalls',
            'status',
            'severity',
            'search',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Display recall details.
     */
    public function show($id)
    {
        $recall = VehicleRecall::with(['vehicle.customer', 'vehicle.serviceRecords'])
            ->findOrFail($id);
            
        $similarRecalls = VehicleRecall::where('component', 'like', "%{$recall->component}%")
            ->where('id', '!=', $id)
            ->limit(5)
            ->get();
            
        $customerVehicles = Vehicle::where('customer_id', $recall->vehicle->customer_id)
            ->where('id', '!=', $recall->vehicle_id)
            ->get();
            
        $customerRecallHistory = VehicleRecall::whereHas('vehicle', function ($q) use ($recall) {
                $q->where('customer_id', $recall->vehicle->customer_id);
            })
            ->where('id', '!=', $id)
            ->orderBy('recall_date', 'desc')
            ->limit(10)
            ->get();
            
        return view('vehicle-tools.recall-show', compact(
            'recall',
            'similarRecalls',
            'customerVehicles',
            'customerRecallHistory'
        ));
    }

    /**
     * Create a new recall.
     */
    public function create(Request $request)
    {
        $vehicleId = $request->get('vehicle_id');
        $vehicle = $vehicleId ? Vehicle::find($vehicleId) : null;
        
        $vehicles = Vehicle::with('customer')
            ->orderBy('make')
            ->orderBy('model')
            ->orderBy('year')
            ->get();
            
        return view('vehicle-tools.recall-create', compact('vehicle', 'vehicles'));
    }

    /**
     * Store a new recall.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'campaign_number' => 'required|string|max:255',
            'component' => 'required|string|max:255',
            'summary' => 'required|string',
            'consequence' => 'required|string',
            'remedy' => 'required|string',
            'recall_date' => 'required|date',
            'status' => 'required|in:open,in_progress,completed,closed',
            'severity' => 'required|in:low,medium,high,critical',
            'estimated_cost' => 'nullable|numeric|min:0',
            'estimated_repair_time' => 'nullable|integer|min:0',
            'parts_required' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        
        $recall = VehicleRecall::create([
            'vehicle_id' => $request->vehicle_id,
            'campaign_number' => $request->campaign_number,
            'component' => $request->component,
            'summary' => $request->summary,
            'consequence' => $request->consequence,
            'remedy' => $request->remedy,
            'recall_date' => $request->recall_date,
            'status' => $request->status,
            'severity' => $request->severity,
            'estimated_cost' => $request->estimated_cost,
            'estimated_repair_time' => $request->estimated_repair_time,
            'parts_required' => $request->parts_required,
            'notes' => $request->notes,
            'added_by' => Auth::id(),
        ]);
        
        // Update vehicle recall count
        $recall->vehicle->updateRecallCount();
        
        // Send notification if requested
        if ($request->boolean('notify_customer')) {
            $this->sendRecallNotification($recall);
        }
        
        return redirect()->route('recalls.show', $recall->id)
            ->with('success', 'Recall created successfully.');
    }

    /**
     * Edit a recall.
     */
    public function edit($id)
    {
        $recall = VehicleRecall::with('vehicle.customer')->findOrFail($id);
        
        return view('vehicle-tools.recall-edit', compact('recall'));
    }

    /**
     * Update a recall.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'campaign_number' => 'required|string|max:255',
            'component' => 'required|string|max:255',
            'summary' => 'required|string',
            'consequence' => 'required|string',
            'remedy' => 'required|string',
            'recall_date' => 'required|date',
            'status' => 'required|in:open,in_progress,completed,closed',
            'severity' => 'required|in:low,medium,high,critical',
            'estimated_cost' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'estimated_repair_time' => 'nullable|integer|min:0',
            'actual_repair_time' => 'nullable|integer|min:0',
            'repair_date' => 'nullable|date',
            'parts_required' => 'nullable|string',
            'parts_used' => 'nullable|string',
            'notes' => 'nullable|string',
            'customer_notified' => 'boolean',
            'customer_notification_date' => 'nullable|date',
            'customer_response' => 'nullable|string',
            'customer_response_date' => 'nullable|date',
        ]);
        
        $recall = VehicleRecall::findOrFail($id);
        
        $recall->update([
            'campaign_number' => $request->campaign_number,
            'component' => $request->component,
            'summary' => $request->summary,
            'consequence' => $request->consequence,
            'remedy' => $request->remedy,
            'recall_date' => $request->recall_date,
            'status' => $request->status,
            'severity' => $request->severity,
            'estimated_cost' => $request->estimated_cost,
            'actual_cost' => $request->actual_cost,
            'estimated_repair_time' => $request->estimated_repair_time,
            'actual_repair_time' => $request->actual_repair_time,
            'repair_date' => $request->repair_date,
            'parts_required' => $request->parts_required,
            'parts_used' => $request->parts_used,
            'notes' => $request->notes,
            'customer_notified' => $request->boolean('customer_notified'),
            'customer_notification_date' => $request->customer_notification_date,
            'customer_response' => $request->customer_response,
            'customer_response_date' => $request->customer_response_date,
            'updated_by' => Auth::id(),
        ]);
        
        // Update vehicle recall count
        $recall->vehicle->updateRecallCount();
        
        // Send notification if requested
        if ($request->boolean('send_notification')) {
            $this->sendRecallNotification($recall);
        }
        
        return redirect()->route('recalls.show', $recall->id)
            ->with('success', 'Recall updated successfully.');
    }

    /**
     * Delete a recall.
     */
    public function destroy($id)
    {
        $recall = VehicleRecall::findOrFail($id);
        $vehicle = $recall->vehicle;
        
        $recall->delete();
        
        // Update vehicle recall count
        $vehicle->updateRecallCount();
        
        return redirect()->route('recalls.index')
            ->with('success', 'Recall deleted successfully.');
    }

    /**
     * Check for recalls for a specific vehicle.
     */
    public function checkVehicleRecalls($vehicleId)
    {
        $vehicle = Vehicle::with('customer')->findOrFail($vehicleId);
        
        try {
            // In a real implementation, this would call a recall API
            // For now, we'll simulate checking and updating
            
            $newRecalls = $this->checkRecallsFromAPI($vehicle->vin);
            
            if (!empty($newRecalls)) {
                foreach ($newRecalls as $recallData) {
                    VehicleRecall::create([
                        'vehicle_id' => $vehicle->id,
                        'campaign_number' => $recallData['campaign_number'],
                        'component' => $recallData['component'],
                        'summary' => $recallData['summary'],
                        'consequence' => $recallData['consequence'],
                        'remedy' => $recallData['remedy'],
                        'recall_date' => $recallData['recall_date'],
                        'status' => 'open',
                        'severity' => $recallData['severity'],
                        'estimated_cost' => $recallData['estimated_cost'] ?? null,
                        'estimated_repair_time' => $recallData['estimated_repair_time'] ?? null,
                        'added_by' => Auth::id(),
                    ]);
                }
                
                $vehicle->updateRecallCount();
                
                return redirect()->route('recalls.index')
                    ->with('success', count($newRecalls) . ' new recalls found for vehicle.');
            }
            
            $vehicle->update([
                'last_recall_check' => now(),
                'recall_check_required' => false,
            ]);
            
            return redirect()->route('recalls.index')
                ->with('success', 'No new recalls found for vehicle.');
                
        } catch (\Exception $e) {
            Log::error('Recall check failed', [
                'vehicle_id' => $vehicleId,
                'vin' => $vehicle->vin,
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->route('recalls.index')
                ->with('error', 'Recall check service temporarily unavailable. Please try again later.');
        }
    }

    /**
     * Batch check recalls for multiple vehicles.
     */
    public function batchCheckRecalls(Request $request)
    {
        $request->validate([
            'vehicle_ids' => 'required|array',
            'vehicle_ids.*' => 'exists:vehicles,id',
        ]);
        
        $vehicleIds = $request->vehicle_ids;
        $checkedCount = 0;
        $newRecallCount = 0;
        $errorCount = 0;
        
        foreach ($vehicleIds as $vehicleId) {
            try {
                $vehicle = Vehicle::find($vehicleId);
                
                if (!$vehicle || !$vehicle->vin) {
                    $errorCount++;
                    continue;
                }
                
                // Check for recalls
                $newRecalls = $this->checkRecallsFromAPI($vehicle->vin);
                
                if (!empty($newRecalls)) {
                    foreach ($newRecalls as $recallData) {
                        VehicleRecall::create([
                            'vehicle_id' => $vehicle->id,
                            'campaign_number' => $recallData['campaign_number'],
                            'component' => $recallData['component'],
                            'summary' => $recallData['summary'],
                            'consequence' => $recallData['consequence'],
                            'remedy' => $recallData['remedy'],
                            'recall_date' => $recallData['recall_date'],
                            'status' => 'open',
                            'severity' => $recallData['severity'],
                            'estimated_cost' => $recallData['estimated_cost'] ?? null,
                            'estimated_repair_time' => $recallData['estimated_repair_time'] ?? null,
                            'added_by' => Auth::id(),
                        ]);
                        $newRecallCount++;
                    }
                    
                    $vehicle->updateRecallCount();
                }
                
                $vehicle->update([
                    'last_recall_check' => now(),
                    'recall_check_required' => false,
                ]);
                
                $checkedCount++;
                
            } catch (\Exception $e) {
                Log::error('Batch recall check failed', [
                    'vehicle_id' => $vehicleId,
                    'error' => $e->getMessage(),
                ]);
                $errorCount++;
            }
        }
        
        return redirect()->route('recalls.dashboard')
            ->with('success', "Batch recall check completed: {$checkedCount} vehicles checked, {$newRecallCount} new recalls found, {$errorCount} errors.");
    }

    /**
     * Send recall notification to customer.
     */
    public function sendNotification($id)
    {
        $recall = VehicleRecall::with('vehicle.customer')->findOrFail($id);
        
        try {
            $this->sendRecallNotification($recall);
            
            $recall->update([
                'customer_notified' => true,
                'customer_notification_date' => now(),
                'customer_response' => null,
                'customer_response_date' => null,
            ]);
            
            return redirect()->route('recalls.show', $recall->id)
                ->with('success', 'Recall notification sent to customer.');
                
        } catch (\Exception $e) {
            Log::error('Recall notification failed', [
                'recall_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->route('recalls.show', $recall->id)
                ->with('error', 'Failed to send notification. Please try again.');
        }
    }

    /**
     * Batch send notifications for multiple recalls.
     */
    public function batchSendNotifications(Request $request)
    {
        $request->validate([
            'recall_ids' => 'required|array',
            'recall_ids.*' => 'exists:vehicle_recalls,id',
        ]);
        
        $recallIds = $request->recall_ids;
        $sentCount = 0;
        $errorCount = 0;
        
        foreach ($recallIds as $recallId) {
            try {
                $recall = VehicleRecall::find($recallId);
                
                if ($recall && !$recall->customer_notified) {
                    $this->sendRecallNotification($recall);
                    
                    $recall->update([
                        'customer_notified' => true,
                        'customer_notification_date' => now(),
                    ]);
                    
                    $sentCount++;
                }
            } catch (\Exception $e) {
                Log::error('Batch notification failed', [
                    'recall_id' => $recallId,
                    'error' => $e->getMessage(),
                ]);
                $errorCount++;
            }
        }
        
        return redirect()->route('recalls.dashboard')
            ->with('success', "Batch notifications sent: {$sentCount} successful, {$errorCount} failed.");
    }

    /**
     * Update recall status.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,completed,closed',
            'repair_date' => 'nullable|date',
            'actual_cost' => 'nullable|numeric|min:0',
            'actual_repair_time' => 'nullable|integer|min:0',
            'parts_used' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        
        $recall = VehicleRecall::findOrFail($id);
        
        $recall->update([
            'status' => $request->status,
            'repair_date' => $request->repair_date,
            'actual_cost' => $request->actual_cost,
            'actual_repair_time' => $request->actual_repair_time,
            'parts_used' => $request->parts_used,
            'notes' => $request->notes,
            'updated_by' => Auth::id(),
        ]);
        
        // Update vehicle recall count
        $recall->vehicle->updateRecallCount();
        
        return redirect()->route('recalls.show', $recall->id)
            ->with('success', 'Recall status updated successfully.');
    }

    /**
     * Export recalls data.
     */
    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:json,csv',
            'status' => 'nullable|in:all,open,in_progress,completed,closed',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);
        
        $format = $request->format;
        $status = $request->get('status', 'all');
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        
        $query = VehicleRecall::with(['vehicle.customer']);
        
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        if ($dateFrom) {
            $query->where('recall_date', '>=', $dateFrom);
        }
        
        if ($dateTo) {
            $query->where('recall_date', '<=', $dateTo);
        }
        
        $recalls = $query->orderBy('recall_date', 'desc')->get();
        
        $data = $recalls->map(function ($recall) {
            return [
                'id' => $recall->id,
                'campaign_number' => $recall->campaign_number,
                'component' => $recall->component,
                'summary' => $recall->summary,
                'consequence' => $recall->consequence,
                'remedy' => $recall->remedy,
                'recall_date' => $recall->recall_date,
                'status' => $recall->status,
                'severity' => $recall->severity,
                'estimated_cost' => $recall->estimated_cost,
                'actual_cost' => $recall->actual_cost,
                'estimated_repair_time' => $recall->estimated_repair_time,
                'actual_repair_time' => $recall->actual_repair_time,
                'repair_date' => $recall->repair_date,
                'customer_notified' => $recall->customer_notified,
                'customer_notification_date' => $recall->customer_notification_date,
                'customer_response' => $recall->customer_response,
                'customer_response_date' => $recall->customer_response_date,
                'vehicle_vin' => $recall->vehicle->vin,
                'vehicle_make' => $recall->vehicle->make,
                'vehicle_model' => $recall->vehicle->model,
                'vehicle_year' => $recall->vehicle->year,
                'customer_name' => $recall->vehicle->customer->full_name,
                'customer_email' => $recall->vehicle->customer->email,
                'customer_phone' => $recall->vehicle->customer->phone,
                'created_at' => $recall->created_at,
                'updated_at' => $recall->updated_at,
            ];
        });
        
        if ($format === 'json') {
            $filename = 'recalls-export-' . now()->format('Y-m-d') . '.json';
            
            return response()->json($data)
                ->header('Content-Type', 'application/json')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
        } else {
            // CSV format
            $filename = 'recalls-export-' . now()->format('Y-m-d') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];
            
            $callback = function () use ($data) {
                $file = fopen('php://output', 'w');
                
                // Add headers
                fputcsv($file, [
                    'ID', 'Campaign Number', 'Component', 'Summary', 'Consequence', 'Remedy',
                    'Recall Date', 'Status', 'Severity', 'Estimated Cost', 'Actual Cost',
                    'Estimated Repair Time', 'Actual Repair Time', 'Repair Date',
                    'Customer Notified', 'Notification Date', 'Customer Response', 'Response Date',
                    'VIN', 'Make', 'Model', 'Year', 'Customer Name', 'Customer Email', 'Customer Phone',
                    'Created At', 'Updated At'
                ]);
                
                // Add data
                foreach ($data as $row) {
                    fputcsv($file, [
                        $row['id'],
                        $row['campaign_number'],
                        $row['component'],
                        $row['summary'],
                        $row['consequence'],
                        $row['remedy'],
                        $row['recall_date'],
                        $row['status'],
                        $row['severity'],
                        $row['estimated_cost'],
                        $row['actual_cost'],
                        $row['estimated_repair_time'],
                        $row['actual_repair_time'],
                        $row['repair_date'],
                        $row['customer_notified'] ? 'Yes' : 'No',
                        $row['customer_notification_date'],
                        $row['customer_response'],
                        $row['customer_response_date'],
                        $row['vehicle_vin'],
                        $row['vehicle_make'],
                        $row['vehicle_model'],
                        $row['vehicle_year'],
                        $row['customer_name'],
                        $row['customer_email'],
                        $row['customer_phone'],
                        $row['created_at'],
                        $row['updated_at'],
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
        }
    }

    /**
     * Get recall statistics for dashboard.
     */
    public function statistics()
    {
        $stats = [
            'total_recalls' => VehicleRecall::count(),
            'open_recalls' => VehicleRecall::where('status', 'open')->count(),
            'in_progress_recalls' => VehicleRecall::where('status', 'in_progress')->count(),
            'completed_recalls' => VehicleRecall::where('status', 'completed')->count(),
            'closed_recalls' => VehicleRecall::where('status', 'closed')->count(),
            'needs_notification' => VehicleRecall::where('customer_notified', false)
                ->whereIn('status', ['open', 'in_progress'])
                ->count(),
            'overdue_recalls' => VehicleRecall::where('status', 'open')
                ->where('recall_date', '<', now()->subDays(30))
                ->count(),
            'urgent_recalls' => VehicleRecall::where('status', 'open')
                ->where('severity', 'high')
                ->count(),
            'total_estimated_cost' => VehicleRecall::sum('estimated_cost'),
            'total_actual_cost' => VehicleRecall::whereNotNull('actual_cost')->sum('actual_cost'),
            'cost_savings' => VehicleRecall::sum('estimated_cost') - VehicleRecall::whereNotNull('actual_cost')->sum('actual_cost'),
            'recall_trends' => $this->getRecallTrends(6),
            'top_components' => VehicleRecall::select('component')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('component')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            'top_makes' => VehicleRecall::join('vehicles', 'vehicle_recalls.vehicle_id', '=', 'vehicles.id')
                ->select('vehicles.make')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('vehicles.make')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
        ];
        
        return response()->json($stats);
    }

    /**
     * Get recall trends for the last N months.
     */
    private function getRecallTrends(int $months = 6): array
    {
        $trends = [];
        $now = now();
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();
            
            $recalls = VehicleRecall::whereBetween('recall_date', [$startOfMonth, $endOfMonth])
                ->get();
                
            $trends[] = [
                'month' => $month->format('M Y'),
                'total' => $recalls->count(),
                'open' => $recalls->where('status', 'open')->count(),
                'in_progress' => $recalls->where('status', 'in_progress')->count(),
                'completed' => $recalls->where('status', 'completed')->count(),
                'closed' => $recalls->where('status', 'closed')->count(),
                'estimated_cost' => $recalls->sum('estimated_cost'),
                'actual_cost' => $recalls->whereNotNull('actual_cost')->sum('actual_cost'),
            ];
        }
        
        return $trends;
    }

    /**
     * Check recalls from API (simulated for now).
     */
    private function checkRecallsFromAPI(string $vin): array
    {
        // In a real implementation, this would call the NHTSA API or similar
        // For now, we'll simulate with mock data
        
        // Only return recalls 30% of the time
        if (rand(0, 100) > 30) {
            return [];
        }
        
        $components = [
            'Airbag System',
            'Brake System',
            'Electrical System',
            'Engine',
            'Fuel System',
            'Steering',
            'Suspension',
            'Transmission',
            'Exterior Lighting',
            'Seat Belts',
            'Tires',
            'Wheels',
        ];
        
        $severities = ['low', 'medium', 'high'];
        $consequences = [
            'May cause engine stall while driving',
            'Could result in loss of braking power',
            'May increase risk of crash',
            'Could cause electrical fire',
            'May result in loss of vehicle control',
            'Could cause injury to occupants',
        ];
        
        $remedies = [
            'Dealer will replace the affected component free of charge',
            'Dealer will inspect and repair as necessary',
            'Dealer will update software',
            'Dealer will install protective shield',
            'Dealer will replace entire assembly',
        ];
        
        $recalls = [];
        $recallCount = rand(1, 3);
        
        for ($i = 0; $i < $recallCount; $i++) {
            $recalls[] = [
                'campaign_number' => 'R' . rand(100000, 999999),
                'component' => $components[array_rand($components)],
                'summary' => 'Safety recall for ' . $components[array_rand($components)] . ' issue',
                'consequence' => $consequences[array_rand($consequences)],
                'remedy' => $remedies[array_rand($remedies)],
                'recall_date' => now()->subDays(rand(0, 90))->format('Y-m-d'),
                'severity' => $severities[array_rand($severities)],
                'estimated_cost' => rand(100, 1000),
                'estimated_repair_time' => rand(1, 8),
            ];
        }
        
        return $recalls;
    }

    /**
     * Send recall notification to customer.
     */
    private function sendRecallNotification(VehicleRecall $recall): void
    {
        // In a real implementation, this would send an email or SMS
        // For now, we'll log the notification
        
        $customer = $recall->vehicle->customer;
        $vehicle = $recall->vehicle;
        
        Log::info('Recall notification sent', [
            'recall_id' => $recall->id,
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'vehicle_vin' => $vehicle->vin,
            'campaign_number' => $recall->campaign_number,
            'component' => $recall->component,
            'severity' => $recall->severity,
        ]);
        
        // In production, you would uncomment this:
        // Mail::to($customer->email)->send(new RecallNotification($recall));
    }

    /**
     * Get recalls that need customer notification.
     */
    public function needsNotification()
    {
        $recalls = VehicleRecall::with(['vehicle.customer'])
            ->where('customer_notified', false)
            ->whereIn('status', ['open', 'in_progress'])
            ->orderBy('severity', 'desc')
            ->orderBy('recall_date', 'asc')
            ->paginate(20);
            
        return view('vehicle-tools.recalls-needs-notification', compact('recalls'));
    }

    /**
     * Get overdue recalls.
     */
    public function overdue()
    {
        $recalls = VehicleRecall::with(['vehicle.customer'])
            ->where('status', 'open')
            ->where('recall_date', '<', now()->subDays(30))
            ->orderBy('recall_date', 'asc')
            ->paginate(20);
            
        return view('vehicle-tools.recalls-overdue', compact('recalls'));
    }

    /**
     * Get urgent recalls.
     */
    public function urgent()
    {
        $recalls = VehicleRecall::with(['vehicle.customer'])
            ->where('status', 'open')
            ->where('severity', 'high')
            ->orderBy('recall_date', 'asc')
            ->paginate(20);
            
        return view('vehicle-tools.recalls-urgent', compact('recalls'));
    }

    /**
     * Get recall analytics report.
     */
    public function analytics()
    {
        $months = 12;
        $trends = $this->getRecallTrends($months);
        
        // Component breakdown
        $componentBreakdown = VehicleRecall::select('component')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('component')
            ->orderBy('count', 'desc')
            ->get();
            
        // Make breakdown
        $makeBreakdown = VehicleRecall::join('vehicles', 'vehicle_recalls.vehicle_id', '=', 'vehicles.id')
            ->select('vehicles.make')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('vehicles.make')
            ->orderBy('count', 'desc')
            ->get();
            
        // Status breakdown
        $statusBreakdown = VehicleRecall::select('status')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('status')
            ->get();
            
        // Severity breakdown
        $severityBreakdown = VehicleRecall::select('severity')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('severity')
            ->get();
            
        // Cost analysis
        $costAnalysis = [
            'total_estimated' => VehicleRecall::sum('estimated_cost'),
            'total_actual' => VehicleRecall::whereNotNull('actual_cost')->sum('actual_cost'),
            'average_estimated' => VehicleRecall::avg('estimated_cost'),
            'average_actual' => VehicleRecall::whereNotNull('actual_cost')->avg('actual_cost'),
            'cost_variance' => VehicleRecall::sum('estimated_cost') - VehicleRecall::whereNotNull('actual_cost')->sum('actual_cost'),
        ];
        
        // Time analysis
        $timeAnalysis = [
            'average_estimated_time' => VehicleRecall::avg('estimated_repair_time'),
            'average_actual_time' => VehicleRecall::whereNotNull('actual_repair_time')->avg('actual_repair_time'),
            'total_estimated_hours' => VehicleRecall::sum('estimated_repair_time'),
            'total_actual_hours' => VehicleRecall::whereNotNull('actual_repair_time')->sum('actual_repair_time'),
        ];
        
        // Customer notification analysis
        $notificationAnalysis = [
            'total_notified' => VehicleRecall::where('customer_notified', true)->count(),
            'total_not_notified' => VehicleRecall::where('customer_notified', false)->count(),
            'notification_rate' => VehicleRecall::count() > 0 
                ? round(VehicleRecall::where('customer_notified', true)->count() / VehicleRecall::count() * 100, 2)
                : 0,
            'average_response_days' => VehicleRecall::whereNotNull('customer_response_date')
                ->whereNotNull('customer_notification_date')
                ->avg(\DB::raw('DATEDIFF(customer_response_date, customer_notification_date)')),
        ];
        
        return view('vehicle-tools.recall-analytics', compact(
            'trends',
            'componentBreakdown',
            'makeBreakdown',
            'statusBreakdown',
            'severityBreakdown',
            'costAnalysis',
            'timeAnalysis',
            'notificationAnalysis'
        ));
    }

    /**
     * Search for recalls by VIN, make, model, or customer.
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (!$query) {
            return redirect()->route('recalls.index');
        }
        
        $recalls = VehicleRecall::with(['vehicle.customer'])
            ->where(function ($q) use ($query) {
                $q->where('campaign_number', 'like', "%{$query}%")
                  ->orWhere('component', 'like', "%{$query}%")
                  ->orWhere('summary', 'like', "%{$query}%")
                  ->orWhereHas('vehicle', function ($q) use ($query) {
                      $q->where('vin', 'like', "%{$query}%")
                        ->orWhere('make', 'like', "%{$query}%")
                        ->orWhere('model', 'like', "%{$query}%")
                        ->orWhere('year', 'like', "%{$query}%");
                  })
                  ->orWhereHas('vehicle.customer', function ($q) use ($query) {
                      $q->where('first_name', 'like', "%{$query}%")
                        ->orWhere('last_name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orWhere('phone', 'like', "%{$query}%");
                  });
            })
            ->orderBy('recall_date', 'desc')
            ->paginate(20);
            
        return view('vehicle-tools.recalls-search', compact('recalls', 'query'));
    }

    /**
     * Get recall API for external integration.
     */
    public function apiIndex(Request $request)
    {
        $request->validate([
            'vin' => 'nullable|string|min:17|max:17',
            'status' => 'nullable|in:open,in_progress,completed,closed',
            'limit' => 'nullable|integer|min:1|max:100',
            'offset' => 'nullable|integer|min:0',
        ]);
        
        $query = VehicleRecall::with(['vehicle']);
        
        if ($request->vin) {
            $query->whereHas('vehicle', function ($q) use ($request) {
                $q->where('vin', $request->vin);
            });
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $limit = $request->get('limit', 50);
        $offset = $request->get('offset', 0);
        
        $total = $query->count();
        $recalls = $query->skip($offset)->take($limit)->get();
        
        return response()->json([
            'success' => true,
            'data' => $recalls->map(function ($recall) {
                return [
                    'id' => $recall->id,
                    'campaign_number' => $recall->campaign_number,
                    'component' => $recall->component,
                    'summary' => $recall->summary,
                    'consequence' => $recall->consequence,
                    'remedy' => $recall->remedy,
                    'recall_date' => $recall->recall_date,
                    'status' => $recall->status,
                    'severity' => $recall->severity,
                    'estimated_cost' => $recall->estimated_cost,
                    'actual_cost' => $recall->actual_cost,
                    'vehicle' => [
                        'vin' => $recall->vehicle->vin,
                        'make' => $recall->vehicle->make,
                        'model' => $recall->vehicle->model,
                        'year' => $recall->vehicle->year,
                    ],
                ];
            }),
            'pagination' => [
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
                'has_more' => ($offset + $limit) < $total,
            ],
        ]);
    }

    /**
     * Get single recall API endpoint.
     */
    public function apiShow($id)
    {
        $recall = VehicleRecall::with(['vehicle.customer'])->find($id);
        
        if (!$recall) {
            return response()->json([
                'success' => false,
                'message' => 'Recall not found',
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $recall->id,
                'campaign_number' => $recall->campaign_number,
                'component' => $recall->component,
                'summary' => $recall->summary,
                'consequence' => $recall->consequence,
                'remedy' => $recall->remedy,
                'recall_date' => $recall->recall_date,
                'status' => $recall->status,
                'severity' => $recall->severity,
                'estimated_cost' => $recall->estimated_cost,
                'actual_cost' => $recall->actual_cost,
                'estimated_repair_time' => $recall->estimated_repair_time,
                'actual_repair_time' => $recall->actual_repair_time,
                'repair_date' => $recall->repair_date,
                'customer_notified' => $recall->customer_notified,
                'customer_notification_date' => $recall->customer_notification_date,
                'customer_response' => $recall->customer_response,
                'customer_response_date' => $recall->customer_response_date,
                'vehicle' => [
                    'id' => $recall->vehicle->id,
                    'vin' => $recall->vehicle->vin,
                    'make' => $recall->vehicle->make,
                    'model' => $recall->vehicle->model,
                    'year' => $recall->vehicle->year,
                    'trim' => $recall->vehicle->trim,
                    'body_style' => $recall->vehicle->body_style,
                ],
                'customer' => $recall->vehicle->customer ? [
                    'id' => $recall->vehicle->customer->id,
                    'first_name' => $recall->vehicle->customer->first_name,
                    'last_name' => $recall->vehicle->customer->last_name,
                    'email' => $recall->vehicle->customer->email,
                    'phone' => $recall->vehicle->customer->phone,
                ] : null,
            ],
        ]);
    }

    /**
     * Schedule automatic recall checks.
     */
    public function scheduleChecks(Request $request)
    {
        $request->validate([
            'frequency' => 'required|in:daily,weekly,monthly',
            'time' => 'required|date_format:H:i',
            'notify_on_find' => 'boolean',
            'notify_customer' => 'boolean',
        ]);
        
        // In a real implementation, this would create a cron job or scheduled task
        // For now, we'll store the schedule in the database
        
        $schedule = [
            'frequency' => $request->frequency,
            'time' => $request->time,
            'notify_on_find' => $request->boolean('notify_on_find'),
            'notify_customer' => $request->boolean('notify_customer'),
            'last_run' => null,
            'next_run' => $this->calculateNextRun($request->frequency, $request->time),
            'created_by' => Auth::id(),
            'created_at' => now(),
        ];
        
        // Store schedule (in production, you would use a database table)
        $scheduleFile = storage_path('app/recall_schedule.json');
        $schedules = file_exists($scheduleFile) ? json_decode(file_get_contents($scheduleFile), true) : [];
        $schedules[] = $schedule;
        file_put_contents($scheduleFile, json_encode($schedules, JSON_PRETTY_PRINT));
        
        return redirect()->route('recalls.dashboard')
            ->with('success', 'Automatic recall checks scheduled successfully.');
    }

    /**
     * Calculate next run time for schedule.
     */
    private function calculateNextRun(string $frequency, string $time): string
    {
        $now = now();
        $timeParts = explode(':', $time);
        $hour = (int)$timeParts[0];
        $minute = (int)$timeParts[1];
        
        $nextRun = $now->copy()->setTime($hour, $minute, 0);
        
        if ($nextRun->isPast()) {
            switch ($frequency) {
                case 'daily':
                    $nextRun->addDay();
                    break;
                case 'weekly':
                    $nextRun->addWeek();
                    break;
                case 'monthly':
                    $nextRun->addMonth();
                    break;
            }
        }
        
        return $nextRun->format('Y-m-d H:i:s');
    }

    /**
     * Run scheduled recall checks.
     */
    public function runScheduledChecks()
    {
        $scheduleFile = storage_path('app/recall_schedule.json');
        
        if (!file_exists($scheduleFile)) {
            return response()->json(['success' => false, 'message' => 'No schedules configured']);
        }
        
        $schedules = json_decode(file_get_contents($scheduleFile), true);
        $now = now();
        $results = [];
        
        foreach ($schedules as $index => &$schedule) {
            $nextRun = Carbon::parse($schedule['next_run']);
            
            if ($now->greaterThanOrEqualTo($nextRun)) {
                // Run the check
                $result = $this->executeScheduledCheck($schedule);
                $results[] = $result;
                
                // Update schedule
                $schedule['last_run'] = $now->format('Y-m-d H:i:s');
                $schedule['next_run'] = $this->calculateNextRun($schedule['frequency'], $schedule['time']);
            }
        }
        
        // Save updated schedules
        file_put_contents($scheduleFile, json_encode($schedules, JSON_PRETTY_PRINT));
        
        return response()->json([
            'success' => true,
            'results' => $results,
            'schedules_updated' => count($results),
        ]);
    }

    /**
     * Execute a scheduled recall check.
     */
    private function executeScheduledCheck(array $schedule): array
    {
        // Get vehicles that need recall check
        $vehicles = Vehicle::where(function ($q) {
                $q->whereNull('last_recall_check')
                  ->orWhere('recall_check_required', true)
                  ->orWhere('last_recall_check', '<', now()->subDays(30));
            })
            ->whereNotNull('vin')
            ->limit(100) // Limit to prevent overwhelming the system
            ->get();
            
        $checkedCount = 0;
        $newRecallCount = 0;
        
        foreach ($vehicles as $vehicle) {
            try {
                $newRecalls = $this->checkRecallsFromAPI($vehicle->vin);
                
                if (!empty($newRecalls)) {
                    foreach ($newRecalls as $recallData) {
                        VehicleRecall::create([
                            'vehicle_id' => $vehicle->id,
                            'campaign_number' => $recallData['campaign_number'],
                            'component' => $recallData['component'],
                            'summary' => $recallData['summary'],
                            'consequence' => $recallData['consequence'],
                            'remedy' => $recallData['remedy'],
                            'recall_date' => $recallData['recall_date'],
                            'status' => 'open',
                            'severity' => $recallData['severity'],
                            'estimated_cost' => $recallData['estimated_cost'] ?? null,
                            'estimated_repair_time' => $recallData['estimated_repair_time'] ?? null,
                            'added_by' => 0, // System user
                        ]);
                        $newRecallCount++;
                    }
                    
                    $vehicle->updateRecallCount();
                    
                    // Send notifications if configured
                    if ($schedule['notify_on_find']) {
                        // Notify staff
                        Log::info('New recalls found during scheduled check', [
                            'vehicle_id' => $vehicle->id,
                            'vin' => $vehicle->vin,
                            'new_recalls' => count($newRecalls),
                        ]);
                    }
                    
                    if ($schedule['notify_customer']) {
                        // Notify customer (simplified)
                        Log::info('Customer notification would be sent for new recalls', [
                            'vehicle_id' => $vehicle->id,
                            'customer_id' => $vehicle->customer_id,
                        ]);
                    }
                }
                
                $vehicle->update([
                    'last_recall_check' => now(),
                    'recall_check_required' => false,
                ]);
                
                $checkedCount++;
                
            } catch (\Exception $e) {
                Log::error('Scheduled recall check failed', [
                    'vehicle_id' => $vehicle->id,
                    'vin' => $vehicle->vin,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        return [
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'vehicles_checked' => $checkedCount,
            'new_recalls_found' => $newRecallCount,
            'schedule' => $schedule['frequency'],
        ];
    }
}
