<?php

namespace App\Http\Controllers;

use App\Models\BlueprintService;
use App\Models\Customer;
use App\Models\JobOrder;
use App\Models\JobOrderItem;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;

/**
 * Admin-facing Job Order routes under /admin/job-orders* (blueprint P6).
 *
 *   - GET  /admin/job-orders          (paginated, filterable by status/customer/date)
 *   - GET  /admin/job-orders/create    (create form)
 *   - POST /admin/job-orders           (store, auto-generate JO-YYYY-NNNN)
 *   - GET  /admin/job-orders/{id}      (detail view)
 */
class AdminJobOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = JobOrder::with(['customer', 'vehicle'])
            ->orderByDesc('job_order_date');

        if ($request->filled('status')) {
            $query->where('job_order_status', $request->get('status'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->get('customer_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('job_order_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('job_order_date', '<=', $request->get('date_to'));
        }

        $jobOrders = $query->paginate(20)->withQueryString();

        return view('admin.job_orders.index', compact('jobOrders'));
    }

    public function create()
    {
        $customers   = Customer::orderBy('first_name')->get();
        $vehicles    = Vehicle::with('customer')->get();
        $technicians = User::orderBy('name')->get();
        $services    = BlueprintService::where('is_active', true)->orderBy('name')->get();

        return view('admin.job_orders.create', compact('customers', 'vehicles', 'technicians', 'services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'      => 'required|exists:customers,id',
            'vehicle_id'       => 'nullable|exists:vehicles,id',
            'job_order_date'   => 'required|date',
            'job_order_status' => 'nullable|in:draft,pending_approval,approved,pending,in_progress,on_hold,completed,cancelled,invoiced',
            'technician_id'    => 'nullable|exists:users,id',
            'internal_notes'   => 'nullable|string|max:5000',
        ]);

        $validated['job_order_status'] = $validated['job_order_status'] ?? 'draft';
        $validated['job_order_number'] = JobOrder::generateAdminJobOrderNumber();
        $validated['job_order_date']   = $validated['job_order_date'];
        $validated['service_advisor_id'] = $request->user()->id;

        $jobOrder = JobOrder::create($validated);

        // Blueprint P6: persist service line-items from the catalog (price override / qty).
        $this->syncItems($jobOrder, $request->input('items', []));

        return redirect()->route('admin.job-orders.index')
            ->with('success', 'Job order created.');
    }

    public function show(JobOrder $jobOrder)
    {
        $jobOrder->load(['customer', 'vehicle', 'items']);

        return view('admin.job_orders.show', compact('jobOrder'));
    }

    /**
     * Blueprint statuses (simple services-only schema): pending -> in_progress -> completed.
     * The legacy heavy schema stored many other status values; P6 restricts admin updates
     * to the blueprint lifecycle so transitions stay canonical and forward-only.
     */
    public const BLUEPRINT_STATUSES = ['pending', 'in_progress', 'completed'];

    /**
     * Validate the shared, blueprint-aligned job order fields.
     */
    private function validateJobOrder(Request $request): array
    {
        return $request->validate([
            'customer_id'      => 'required|exists:customers,id',
            'vehicle_id'       => 'nullable|exists:vehicles,id',
            'job_order_date'   => 'sometimes|date',
            'job_order_status' => 'nullable|in:' . implode(',', self::BLUEPRINT_STATUSES),
            'internal_notes'   => 'nullable|string|max:5000',
            'technician_id'    => 'nullable|exists:users,id',
        ]);
    }

    /**
     * GET /admin/job-orders/{job_order}/edit — edit form.
     */
    public function edit(JobOrder $jobOrder)
    {
        $jobOrder->load(['customer', 'vehicle', 'items']);

        $customers   = Customer::orderBy('first_name')->get();
        $vehicles    = Vehicle::with('customer')->get();
        $technicians = User::orderBy('name')->get();
        $services    = BlueprintService::where('is_active', true)->orderBy('name')->get();
        $statuses    = self::BLUEPRINT_STATUSES;

        return view('admin.job_orders.edit', compact('jobOrder', 'customers', 'vehicles', 'technicians', 'services', 'statuses'));
    }

    /**
     * PUT /admin/job-orders/{job_order} — update notes, status, technician.
     */
    public function update(Request $request, JobOrder $jobOrder)
    {
        $validated = $this->validateJobOrder($request);

        // Forward-only status transition guard (blueprint: pending -> in_progress -> completed).
        if (isset($validated['job_order_status'])
            && $validated['job_order_status'] !== $jobOrder->job_order_status
            && ! $this->canTransition($jobOrder->job_order_status, $validated['job_order_status'])) {
            return back()->withErrors([
                'job_order_status' => "Invalid status transition from {$jobOrder->job_order_status} to {$validated['job_order_status']}.",
            ]);
        }

        $jobOrder->update($validated);

        // Blueprint P6: replace service line-items from the catalog when supplied.
        if ($request->has('items')) {
            $this->syncItems($jobOrder, $request->input('items', []));
        }

        return redirect()->route('admin.job-orders.index')
            ->with('success', 'Job order updated.');
    }

    /**
     * DELETE /admin/job-orders/{job_order} — soft delete.
     */
    public function destroy(JobOrder $jobOrder)
    {
        $jobOrder->delete();

        return redirect()->route('admin.job-orders.index')
            ->with('success', 'Job order deleted.');
    }

    /**
     * GET /admin/job-orders/{job_order}/print — Version A: full JO with pricing.
     *
     * Blueprint P7: full job order + service prices (Admin / Client copy).
     * Generates a PDF download via dompdf.
     */
    public function print(JobOrder $jobOrder)
    {
        $jobOrder->load(['customer', 'vehicle', 'items', 'technician']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.job-order-version-a', [
            'jobOrder' => $jobOrder,
        ])->setPaper('letter');

        return $pdf->download('job-order-' . $jobOrder->job_order_number . '.pdf');
    }

    /**
     * GET /admin/job-orders/{job_order}/print/tech — Version B (tech only).
     *
     * Blueprint P7: services + tech name ONLY, NO pricing / NO address / NO phone / NO email.
     */
    public function printTech(JobOrder $jobOrder)
    {
        $jobOrder->load(['items', 'technician', 'vehicle']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.job-order-version-b-tech', [
            'jobOrder' => $jobOrder,
        ])->setPaper('letter');

        return $pdf->download('job-order-' . $jobOrder->job_order_number . '-tech.pdf');
    }

    /**
     * Blueprint lifecycle check: only forward transitions among pending/in_progress/completed.
     * A job already in a terminal state (completed) cannot move backward or forward out.
     */
    private function canTransition(string $from, string $to): bool
    {
        $order = array_flip(self::BLUEPRINT_STATUSES);

        return isset($order[$from], $order[$to]) && $order[$to] > $order[$from];
    }

    /**
     * Blueprint P6: replace a job order's service line-items from catalog data.
     *
     * Each row: catalog_id (services.id), quantity, unit_price (nullable override).
     * - No override => use the catalog default_price (item 3).
     * - Override   => use the supplied unit_price (item 4).
     * - Quantity   => drives total_cost (item 5).
     * Rows missing a valid catalog_id are skipped.
     */
    private function syncItems(JobOrder $jobOrder, array $items): void
    {
        $jobOrder->items()->delete();

        foreach ($items as $row) {
            if (! is_array($row) || empty($row['catalog_id'])) {
                continue;
            }

            $service = BlueprintService::find($row['catalog_id']);
            if (! $service) {
                continue;
            }

            $quantity   = max(0, (float) ($row['quantity'] ?? 1));
            $unitPrice  = isset($row['unit_price']) && $row['unit_price'] !== null && $row['unit_price'] !== ''
                ? (float) $row['unit_price']
                : (float) $service->default_price;

            $item = new JobOrderItem([
                'job_order_id' => $jobOrder->id,
                'item_type'    => 'labor',
                'description'  => $service->name,
                'part_number'  => null,
                'quantity'     => $quantity,
                'unit'         => 'each',
                'unit_cost'    => $unitPrice,
            ]);

            $item->calculateTotals();
            $item->save();
        }
    }
}
