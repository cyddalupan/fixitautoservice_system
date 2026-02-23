<?php

namespace App\Http\Controllers;

use App\Models\PartsRequest;
use App\Models\PartsRequestItem;
use App\Models\WorkOrder;
use App\Models\Vehicle;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PartsRequestController extends Controller
{
    /**
     * Display a listing of parts requests.
     */
    public function index(Request $request)
    {
        $query = PartsRequest::with(['technician', 'workOrder', 'vehicle']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }

        // Filter by technician
        if ($request->has('technician_id') && $request->technician_id) {
            $query->where('technician_id', $request->technician_id);
        }

        // Filter by work order
        if ($request->has('work_order_id') && $request->work_order_id) {
            $query->where('work_order_id', $request->work_order_id);
        }

        // Filter by search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('request_number', 'like', '%' . $request->search . '%')
                  ->orWhere('notes', 'like', '%' . $request->search . '%')
                  ->orWhereHas('technician', function ($q2) use ($request) {
                      $q2->where('name', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('vehicle', function ($q2) use ($request) {
                      $q2->where('make', 'like', '%' . $request->search . '%')
                         ->orWhere('model', 'like', '%' . $request->search . '%')
                         ->orWhere('license_plate', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Sort by priority and date
        $query->orderByRaw("FIELD(priority, 'high', 'normal', 'low')")
              ->orderBy('requested_at', 'desc');

        $requests = $query->paginate(15);

        return view('parts-requests.index', [
            'requests' => $requests,
            'technicians' => User::technicians()->active()->get(),
            'workOrders' => WorkOrder::where('status', '!=', 'completed')->get(),
        ]);
    }

    /**
     * Show the form for creating a new parts request.
     */
    public function create(Request $request)
    {
        // Get work order if specified
        $workOrder = null;
        $vehicle = null;
        
        if ($request->has('work_order_id')) {
            $workOrder = WorkOrder::findOrFail($request->work_order_id);
            $vehicle = $workOrder->vehicle;
        }

        return view('parts-requests.create', [
            'workOrder' => $workOrder,
            'vehicle' => $vehicle,
            'workOrders' => WorkOrder::where('status', '!=', 'completed')->get(),
            'vehicles' => Vehicle::all(),
            'inventory' => Inventory::where('quantity_on_hand', '>', 0)->get(),
        ]);
    }

    /**
     * Store a newly created parts request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'work_order_id' => 'required|exists:work_orders,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'priority' => 'required|in:high,normal,low',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.part_number' => 'required|string|max:100',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.inventory_id' => 'nullable|exists:inventory,id',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        // Create parts request
        $partsRequest = PartsRequest::create([
            'technician_id' => Auth::id(),
            'work_order_id' => $request->work_order_id,
            'vehicle_id' => $request->vehicle_id,
            'priority' => $request->priority,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        // Create items
        foreach ($request->items as $itemData) {
            $item = PartsRequestItem::create([
                'parts_request_id' => $partsRequest->id,
                'part_number' => $itemData['part_number'],
                'description' => $itemData['description'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'inventory_id' => $itemData['inventory_id'] ?? null,
                'status' => 'requested',
                'notes' => $itemData['notes'] ?? null,
            ]);

            // Update total price
            $item->updateTotalPrice();
        }

        // Update total cost
        $partsRequest->updateTotalCost();

        return redirect()->route('parts-requests.show', $partsRequest)
            ->with('success', 'Parts request created successfully.');
    }

    /**
     * Display the specified parts request.
     */
    public function show(PartsRequest $partsRequest)
    {
        $partsRequest->load([
            'technician',
            'workOrder',
            'vehicle',
            'approver',
            'orderer',
            'receiver',
            'installer',
            'items.inventory'
        ]);

        return view('parts-requests.show', [
            'request' => $partsRequest,
        ]);
    }

    /**
     * Approve a parts request.
     */
    public function approve(PartsRequest $partsRequest)
    {
        // Only managers and admins can approve
        if (!Auth::user()->isAdmin() && !Auth::user()->isManager()) {
            abort(403);
        }

        if (!$partsRequest->isPending()) {
            return redirect()->back()->with('error', 'Only pending requests can be approved.');
        }

        $partsRequest->approve(Auth::id());

        return redirect()->back()->with('success', 'Parts request approved successfully.');
    }

    /**
     * Mark parts request as ordered.
     */
    public function markAsOrdered(PartsRequest $partsRequest)
    {
        // Only managers, admins, and service advisors can mark as ordered
        if (!Auth::user()->isAdmin() && !Auth::user()->isManager() && !Auth::user()->isServiceAdvisor()) {
            abort(403);
        }

        if (!$partsRequest->isApproved()) {
            return redirect()->back()->with('error', 'Only approved requests can be marked as ordered.');
        }

        $partsRequest->markAsOrdered(Auth::id());

        return redirect()->back()->with('success', 'Parts request marked as ordered.');
    }

    /**
     * Mark parts request as received.
     */
    public function markAsReceived(PartsRequest $partsRequest)
    {
        // Only managers, admins, and service advisors can mark as received
        if (!Auth::user()->isAdmin() && !Auth::user()->isManager() && !Auth::user()->isServiceAdvisor()) {
            abort(403);
        }

        if (!$partsRequest->isOrdered()) {
            return redirect()->back()->with('error', 'Only ordered requests can be marked as received.');
        }

        $partsRequest->markAsReceived(Auth::id());

        return redirect()->back()->with('success', 'Parts request marked as received.');
    }

    /**
     * Mark parts request as installed.
     */
    public function markAsInstalled(PartsRequest $partsRequest)
    {
        // Only technicians, managers, and admins can mark as installed
        if (!Auth::user()->isAdmin() && !Auth::user()->isManager() && !Auth::user()->isTechnician()) {
            abort(403);
        }

        if (!$partsRequest->isReceived()) {
            return redirect()->back()->with('error', 'Only received requests can be marked as installed.');
        }

        $partsRequest->markAsInstalled(Auth::id());

        return redirect()->back()->with('success', 'Parts request marked as installed.');
    }

    /**
     * Display dashboard for parts requests.
     */
    public function dashboard()
    {
        // Only managers and admins can access
        if (!Auth::user()->isAdmin() && !Auth::user()->isManager()) {
            abort(403);
        }

        // Get statistics
        $totalRequests = PartsRequest::count();
        $pendingRequests = PartsRequest::pending()->count();
        $approvedRequests = PartsRequest::approved()->count();
        $orderedRequests = PartsRequest::ordered()->count();
        $receivedRequests = PartsRequest::received()->count();
        $installedRequests = PartsRequest::installed()->count();

        // Get high priority requests
        $highPriorityRequests = PartsRequest::highPriority()->pending()->get();

        // Get recent requests
        $recentRequests = PartsRequest::with(['technician', 'workOrder', 'vehicle'])
            ->orderBy('requested_at', 'desc')
            ->limit(10)
            ->get();

        // Get technician request counts
        $technicianStats = User::technicians()->active()
            ->withCount(['partsRequests as pending_requests_count' => function ($query) {
                $query->where('status', 'pending');
            }])
            ->withCount(['partsRequests as total_requests_count'])
            ->get()
            ->sortByDesc('total_requests_count');

        return view('parts-requests.dashboard', [
            'totalRequests' => $totalRequests,
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
            'orderedRequests' => $orderedRequests,
            'receivedRequests' => $receivedRequests,
            'installedRequests' => $installedRequests,
            'highPriorityRequests' => $highPriorityRequests,
            'recentRequests' => $recentRequests,
            'technicianStats' => $technicianStats,
        ]);
    }

    /**
     * Get parts request statistics.
     */
    public function statistics()
    {
        // Only managers and admins can access
        if (!Auth::user()->isAdmin() && !Auth::user()->isManager()) {
            abort(403);
        }

        // Get monthly statistics
        $monthlyStats = PartsRequest::selectRaw('
            DATE_FORMAT(requested_at, "%Y-%m") as month,
            COUNT(*) as total_requests,
            SUM(total_cost) as total_cost,
            AVG(total_cost) as avg_cost
        ')
        ->where('requested_at', '>=', now()->subMonths(6))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        // Get status distribution
        $statusDistribution = PartsRequest::selectRaw('
            status,
            COUNT(*) as count,
            ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM parts_requests), 1) as percentage
        ')
        ->groupBy('status')
        ->get();

        // Get priority distribution
        $priorityDistribution = PartsRequest::selectRaw('
            priority,
            COUNT(*) as count,
            ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM parts_requests), 1) as percentage
        ')
        ->groupBy('priority')
        ->get();

        return view('parts-requests.statistics', [
            'monthlyStats' => $monthlyStats,
            'statusDistribution' => $statusDistribution,
            'priorityDistribution' => $priorityDistribution,
        ]);
    }
}