<?php

namespace App\Http\Controllers;

use App\Models\CustomerSatisfactionSurvey;
use App\Models\WorkOrder;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomerSatisfactionController extends Controller
{
    /**
     * Display a listing of customer satisfaction surveys.
     */
    public function index(Request $request)
    {
        $query = CustomerSatisfactionSurvey::with(['workOrder', 'customer', 'technician', 'followUpUser']);

        // Apply filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('sentiment') && $request->sentiment) {
            switch ($request->sentiment) {
                case 'positive':
                    $query->positive();
                    break;
                case 'negative':
                    $query->negative();
                    break;
                case 'neutral':
                    $query->where(function ($q) {
                        $q->where('overall_rating', 3)
                          ->orWhere(function ($q2) {
                              $q2->where('overall_rating', '>', 2)
                                 ->where('overall_rating', '<', 4);
                          });
                    });
                    break;
            }
        }

        if ($request->has('customer_id') && $request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('technician_id') && $request->technician_id) {
            $query->where('technician_id', $request->technician_id);
        }

        if ($request->has('work_order_id') && $request->work_order_id) {
            $query->where('work_order_id', $request->work_order_id);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($q2) use ($search) {
                    $q2->where('first_name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%");
                })
                ->orWhereHas('workOrder', function ($q2) use ($search) {
                    $q2->where('work_order_number', 'like', "%{$search}%");
                })
                ->orWhere('positive_comments', 'like', "%{$search}%")
                ->orWhere('improvement_suggestions', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $surveys = $query->paginate(20);

        $statuses = CustomerSatisfactionSurvey::getStatuses();
        $customers = Customer::where('is_active', true)->orderBy('first_name')->get();
        $technicians = User::where('is_active', true)->where('role', 'technician')->orderBy('name')->get();

        return view('customer-satisfaction.index', compact('surveys', 'statuses', 'customers', 'technicians'));
    }

    /**
     * Show the form for creating a new customer satisfaction survey.
     */
    public function create(Request $request)
    {
        $workOrderId = $request->get('work_order_id');
        $workOrder = null;
        $customer = null;
        $technician = null;

        if ($workOrderId) {
            $workOrder = WorkOrder::with(['customer', 'assignedTechnician'])->find($workOrderId);
            if ($workOrder) {
                $customer = $workOrder->customer;
                $technician = $workOrder->assignedTechnician;
            }
        }

        $workOrders = WorkOrder::where('status', 'completed')
            ->whereDoesntHave('customerSatisfactionSurvey')
            ->with(['customer', 'assignedTechnician'])
            ->orderBy('completed_at', 'desc')
            ->get();

        $customers = Customer::where('is_active', true)->orderBy('first_name')->get();
        $technicians = User::where('is_active', true)->where('role', 'technician')->orderBy('name')->get();

        return view('customer-satisfaction.create', compact(
            'workOrder', 'customer', 'technician', 'workOrders', 'customers', 'technicians'
        ));
    }

    /**
     * Store a newly created customer satisfaction survey.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'work_order_id' => 'nullable|exists:work_orders,id',
            'customer_id' => 'required|exists:customers,id',
            'technician_id' => 'nullable|exists:users,id',
            'overall_rating' => 'required|integer|min:1|max:5',
            'quality_rating' => 'required|integer|min:1|max:5',
            'timeliness_rating' => 'required|integer|min:1|max:5',
            'communication_rating' => 'required|integer|min:1|max:5',
            'cleanliness_rating' => 'required|integer|min:1|max:5',
            'value_rating' => 'required|integer|min:1|max:5',
            'positive_comments' => 'nullable|string',
            'improvement_suggestions' => 'nullable|string',
            'would_recommend' => 'boolean',
            'would_return' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        $data['status'] = CustomerSatisfactionSurvey::STATUS_COMPLETED;
        $data['completed_at'] = now();

        $survey = CustomerSatisfactionSurvey::create($data);

        // Auto-detect if follow-up is needed
        if ($survey->needsFollowUp()) {
            $survey->status = CustomerSatisfactionSurvey::STATUS_FOLLOW_UP_NEEDED;
            $survey->save();
        }

        return redirect()->route('customer-satisfaction.show', $survey->id)
            ->with('success', 'Customer satisfaction survey created successfully.');
    }

    /**
     * Display the specified customer satisfaction survey.
     */
    public function show($id)
    {
        $survey = CustomerSatisfactionSurvey::with([
            'workOrder', 
            'customer', 
            'technician', 
            'followUpUser'
        ])->findOrFail($id);

        $ratingBreakdown = $survey->getRatingBreakdown();

        return view('customer-satisfaction.show', compact('survey', 'ratingBreakdown'));
    }

    /**
     * Show the form for editing the specified customer satisfaction survey.
     */
    public function edit($id)
    {
        $survey = CustomerSatisfactionSurvey::findOrFail($id);
        $customers = Customer::where('is_active', true)->orderBy('first_name')->get();
        $technicians = User::where('is_active', true)->where('role', 'technician')->orderBy('name')->get();
        $workOrders = WorkOrder::where('status', 'completed')->with(['customer', 'assignedTechnician'])->get();

        return view('customer-satisfaction.edit', compact('survey', 'customers', 'technicians', 'workOrders'));
    }

    /**
     * Update the specified customer satisfaction survey.
     */
    public function update(Request $request, $id)
    {
        $survey = CustomerSatisfactionSurvey::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'work_order_id' => 'nullable|exists:work_orders,id',
            'customer_id' => 'required|exists:customers,id',
            'technician_id' => 'nullable|exists:users,id',
            'overall_rating' => 'required|integer|min:1|max:5',
            'quality_rating' => 'required|integer|min:1|max:5',
            'timeliness_rating' => 'required|integer|min:1|max:5',
            'communication_rating' => 'required|integer|min:1|max:5',
            'cleanliness_rating' => 'required|integer|min:1|max:5',
            'value_rating' => 'required|integer|min:1|max:5',
            'positive_comments' => 'nullable|string',
            'improvement_suggestions' => 'nullable|string',
            'would_recommend' => 'boolean',
            'would_return' => 'boolean',
            'status' => 'required|in:pending,completed,follow_up_needed,resolved',
            'follow_up_notes' => 'nullable|string',
            'follow_up_by' => 'nullable|exists:users,id',
            'follow_up_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // If marking as resolved, set follow-up details
        if ($data['status'] === CustomerSatisfactionSurvey::STATUS_RESOLVED && empty($survey->follow_up_date)) {
            $data['follow_up_date'] = now();
            $data['follow_up_by'] = Auth::id();
        }

        $survey->update($data);

        return redirect()->route('customer-satisfaction.show', $survey->id)
            ->with('success', 'Customer satisfaction survey updated successfully.');
    }

    /**
     * Remove the specified customer satisfaction survey.
     */
    public function destroy($id)
    {
        $survey = CustomerSatisfactionSurvey::findOrFail($id);
        $survey->delete();

        return redirect()->route('customer-satisfaction.index')
            ->with('success', 'Customer satisfaction survey deleted successfully.');
    }

    /**
     * Mark survey as resolved.
     */
    public function markAsResolved(Request $request, $id)
    {
        $survey = CustomerSatisfactionSurvey::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'follow_up_notes' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $survey->markAsResolved(Auth::id(), $request->follow_up_notes);

        return redirect()->route('customer-satisfaction.show', $survey->id)
            ->with('success', 'Survey marked as resolved successfully.');
    }

    /**
     * Send survey to customer (simulate email).
     */
    public function sendSurvey($id)
    {
        $survey = CustomerSatisfactionSurvey::with('customer')->findOrFail($id);

        // In a real implementation, this would send an email
        // For now, we'll just update the status
        $survey->status = CustomerSatisfactionSurvey::STATUS_PENDING;
        $survey->save();

        return redirect()->back()
            ->with('success', 'Survey sent to customer successfully.');
    }

    /**
     * Auto-create surveys for completed work orders.
     */
    public function autoCreateSurveys()
    {
        // Find work orders completed in the last 24 hours without surveys
        $workOrders = WorkOrder::where('status', 'completed')
            ->where('completed_at', '>=', now()->subDays(1))
            ->whereDoesntHave('customerSatisfactionSurvey')
            ->with(['customer', 'assignedTechnician'])
            ->get();

        $createdCount = 0;

        foreach ($workOrders as $workOrder) {
            // Check if customer is active and has email
            if ($workOrder->customer && $workOrder->customer->is_active && $workOrder->customer->email) {
                CustomerSatisfactionSurvey::create([
                    'work_order_id' => $workOrder->id,
                    'customer_id' => $workOrder->customer_id,
                    'technician_id' => $workOrder->assigned_technician_id,
                    'status' => CustomerSatisfactionSurvey::STATUS_PENDING,
                ]);
                $createdCount++;
            }
        }

        return redirect()->route('customer-satisfaction.index')
            ->with('success', "{$createdCount} surveys auto-created for completed work orders.");
    }

    /**
     * Bulk update survey status.
     */
    public function bulkUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'survey_ids' => 'required|array',
            'survey_ids.*' => 'exists:customer_satisfaction_surveys,id',
            'action' => 'required|in:send,mark_resolved,delete',
            'follow_up_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'Invalid request.');
        }

        $surveyIds = $request->survey_ids;
        $action = $request->action;
        $count = 0;

        switch ($action) {
            case 'send':
                CustomerSatisfactionSurvey::whereIn('id', $surveyIds)
                    ->update(['status' => CustomerSatisfactionSurvey::STATUS_PENDING]);
                $message = 'Selected surveys sent successfully.';
                $count = count($surveyIds);
                break;
            case 'mark_resolved':
                foreach ($surveyIds as $surveyId) {
                    $survey = CustomerSatisfactionSurvey::find($surveyId);
                    if ($survey) {
                        $survey->markAsResolved(Auth::id(), $request->follow_up_notes ?? 'Bulk resolution');
                        $count++;
                    }
                }
                $message = "{$count} surveys marked as resolved.";
                break;
            case 'delete':
                CustomerSatisfactionSurvey::whereIn('id', $surveyIds)->delete();
                $message = 'Selected surveys deleted successfully.';
                $count = count($surveyIds);
                break;
        }

        return redirect()->back()
            ->with('success', $message);
    }

    /**
     * Export surveys to CSV.
     */
    public function export(Request $request)
    {
        $query = CustomerSatisfactionSurvey::with(['workOrder', 'customer', 'technician']);

        // Apply filters same as index
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('sentiment') && $request->sentiment) {
            switch ($request->sentiment) {
                case 'positive':
                    $query->positive();
                    break;
                case 'negative':
                    $query->negative();
                    break;
            }
        }

        $surveys = $query->get();

        $csvData = [];
        $csvData[] = [
            'Survey ID', 'Work Order', 'Customer', 'Technician', 'Overall Rating', 
            'Quality Rating', 'Timeliness Rating', 'Communication Rating', 
            'Cleanliness Rating', 'Value Rating', 'Average Rating', 'Sentiment',
            'Would Recommend', 'Would Return', 'Status', 'Positive Comments',
            'Improvement Suggestions', 'Follow-up Notes', 'Created Date'
        ];

        foreach ($surveys as $survey) {
            $csvData[] = [
                $survey->id,
                $survey->workOrder ? $survey->workOrder->work_order_number : 'N/A',
                $survey->customer ? $survey->customer->full_name : 'N/A',
                $survey->technician ? $survey->technician->name : 'N/A',
                $survey->overall_rating,
                $survey->quality_rating,
                $survey->timeliness_rating,
                $survey->communication_rating,
                $survey->cleanliness_rating,
                $survey->value_rating,
                $survey->calculateAverageRating(),
                $survey->getSentimentLabel(),
                $survey->would_recommend ? 'Yes' : 'No',
                $survey->would_return ? 'Yes' : 'No',
                $survey->getStatusLabel(),
                $survey->positive_comments,
                $survey->improvement_suggestions,
                $survey->follow_up_notes,
                $survey->created_at->format('Y-m-d H:i:s'),
            ];
        }

        $filename = 'customer_satisfaction_surveys_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Dashboard with survey statistics.
     */
    public function dashboard()
    {
        $totalSurveys = CustomerSatisfactionSurvey::count();
        $pendingSurveys = CustomerSatisfactionSurvey::pending()->count();
        $completedSurveys = CustomerSatisfactionSurvey::completed()->count();
        $needsFollowUpSurveys = CustomerSatisfactionSurvey::needsFollowUp()->count();
        $resolvedSurveys = CustomerSatisfactionSurvey::where('status', 'resolved')->count();

        // Sentiment breakdown
        $positiveSurveys = CustomerSatisfactionSurvey::positive()->count();
        $negativeSurveys = CustomerSatisfactionSurvey::negative()->count();
        $neutralSurveys = $totalSurveys - $positiveSurveys - $negativeSurveys;

        // Average ratings
        $averageRatings = [
            'overall' => CustomerSatisfactionSurvey::where('overall_rating', '>', 0)->avg('overall_rating') ?? 0,
            'quality' => CustomerSatisfactionSurvey::where('quality_rating', '>', 0)->avg('quality_rating') ?? 0,
            'timeliness' => CustomerSatisfactionSurvey::where('timeliness_rating', '>', 0)->avg('timeliness_rating') ?? 0,
            'communication' => CustomerSatisfactionSurvey::where('communication_rating', '>', 0)->avg('communication_rating') ?? 0,
            'cleanliness' => CustomerSatisfactionSurvey::where('cleanliness_rating', '>', 0)->avg('cleanliness_rating') ?? 0,
            'value' => CustomerSatisfactionSurvey::where('value_rating', '>', 0)->avg('value_rating') ?? 0,
        ];

        // Recommendation rates
        $recommendationRate = $totalSurveys > 0 
            ? round(CustomerSatisfactionSurvey::where('would_recommend', true)->count() / $totalSurveys * 100, 2)
            : 0;
        
        $returnRate = $totalSurveys > 0
            ? round(CustomerSatisfactionSurvey::where('would_return', true)->count() / $totalSurveys * 100, 2)
            : 0;

        // Recent surveys
        $recentSurveys = CustomerSatisfactionSurvey::with(['customer', 'technician'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Surveys needing immediate follow-up
        $urgentFollowUp = CustomerSatisfactionSurvey::needsFollowUp()
            ->with(['customer', 'technician'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Top technicians by rating
        $topTechnicians = User::where('role', 'technician')
            ->whereHas('customerSatisfactionSurveys')
            ->withCount(['customerSatisfactionSurveys as average_rating' => function ($query) {
                $query->select(\DB::raw('AVG(overall_rating)'));
            }])
            ->orderBy('average_rating', 'desc')
            ->limit(5)
            ->get();

        return view('customer-satisfaction.dashboard', compact(
            'totalSurveys',
            'pendingSurveys',
            'completedSurveys',
            'needsFollowUpSurveys',
            'resolvedSurveys',
            'positiveSurveys',
            'negativeSurveys',
            'neutralSurveys',
            'averageRatings',
            'recommendationRate',
            'returnRate',
            'recentSurveys',
            'urgentFollowUp',
            'topTechnicians'
        ));
    }

    /**
     * Get survey statistics for charts.
     */
    public function getStatistics(Request $request)
    {
        $period = $request->get('period', 'month'); // day, week, month, year

        $query = CustomerSatisfactionSurvey::query();

        switch ($period) {
            case 'day':
                $query->whereDate('created_at', now()->toDateString());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
            case 'year':
                $query->whereYear('created_at', now()->year);
                break;
        }

        $total = $query->count();
        $positive = $query->positive()->count();
        $negative = $query->negative()->count();
        $neutral = $total - $positive - $negative;

        $averageRating = $total > 0 
            ? round($query->avg('overall_rating'), 2)
            : 0;

        $recommendationRate = $total > 0
            ? round($query->where('would_recommend', true)->count() / $total * 100, 2)
            : 0;

        return response()->json([
            'total' => $total,
            'positive' => $positive,
            'negative' => $negative,
            'neutral' => $neutral,
            'average_rating' => $averageRating,
            'recommendation_rate' => $recommendationRate,
            'period' => $period,
        ]);
    }
}