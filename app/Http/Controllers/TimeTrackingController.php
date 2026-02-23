<?php

namespace App\Http\Controllers;

use App\Models\TimeLog;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TimeTrackingController extends Controller
{
    /**
     * Display a listing of the time logs.
     */
    public function index(Request $request)
    {
        $query = TimeLog::with(['technician', 'workOrder', 'appointment', 'approver'])
            ->orderBy('log_time', 'desc');

        // Filter by technician
        if ($request->has('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('log_time', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('log_time', '<=', $request->end_date);
        }

        // Filter by log type
        if ($request->has('log_type')) {
            $query->where('log_type', $request->log_type);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by work order
        if ($request->has('work_order_id')) {
            $query->where('work_order_id', $request->work_order_id);
        }

        $timeLogs = $query->paginate(20);

        return view('time-tracking.index', compact('timeLogs'));
    }

    /**
     * Show the form for creating a new time log.
     */
    public function create()
    {
        $technicians = User::where('role', 'technician')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $workOrders = WorkOrder::whereIn('status', ['in_progress', 'scheduled'])
            ->orderBy('created_at', 'desc')
            ->get();

        $appointments = Appointment::whereIn('status', ['scheduled', 'in_progress'])
            ->orderBy('appointment_date', 'desc')
            ->get();

        $logTypes = TimeLog::getLogTypes();

        return view('time-tracking.create', compact('technicians', 'workOrders', 'appointments', 'logTypes'));
    }

    /**
     * Store a newly created time log in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'technician_id' => 'required|exists:users,id',
            'log_type' => 'required|in:' . implode(',', array_keys(TimeLog::getLogTypes())),
            'log_time' => 'required|date',
            'work_order_id' => 'nullable|exists:work_orders,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = $request->all();
            $data['ip_address'] = $request->ip();
            
            // If user is clocking themselves in/out, use their ID
            if (Auth::user()->role === 'technician' && Auth::id() == $request->technician_id) {
                $data['device_id'] = 'web_' . $request->ip();
            }

            $timeLog = TimeLog::createLog($data);

            return redirect()->route('time-tracking.show', $timeLog->id)
                ->with('success', 'Time log created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified time log.
     */
    public function show(TimeLog $timeLog)
    {
        $timeLog->load(['technician', 'workOrder', 'appointment', 'approver']);
        
        return view('time-tracking.show', compact('timeLog'));
    }

    /**
     * Show the form for editing the specified time log.
     */
    public function edit(TimeLog $timeLog)
    {
        // Only allow editing of pending logs
        if (!$timeLog->isPending()) {
            return redirect()->route('time-tracking.show', $timeLog->id)
                ->with('error', 'Only pending time logs can be edited.');
        }

        $technicians = User::where('role', 'technician')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $workOrders = WorkOrder::whereIn('status', ['in_progress', 'scheduled'])
            ->orderBy('created_at', 'desc')
            ->get();

        $appointments = Appointment::whereIn('status', ['scheduled', 'in_progress'])
            ->orderBy('appointment_date', 'desc')
            ->get();

        $logTypes = TimeLog::getLogTypes();

        return view('time-tracking.edit', compact('timeLog', 'technicians', 'workOrders', 'appointments', 'logTypes'));
    }

    /**
     * Update the specified time log in storage.
     */
    public function update(Request $request, TimeLog $timeLog)
    {
        // Only allow updating of pending logs
        if (!$timeLog->isPending()) {
            return redirect()->route('time-tracking.show', $timeLog->id)
                ->with('error', 'Only pending time logs can be updated.');
        }

        $validator = Validator::make($request->all(), [
            'technician_id' => 'required|exists:users,id',
            'log_type' => 'required|in:' . implode(',', array_keys(TimeLog::getLogTypes())),
            'log_time' => 'required|date',
            'work_order_id' => 'nullable|exists:work_orders,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $timeLog->update($request->all());

        return redirect()->route('time-tracking.show', $timeLog->id)
            ->with('success', 'Time log updated successfully.');
    }

    /**
     * Remove the specified time log from storage.
     */
    public function destroy(TimeLog $timeLog)
    {
        // Only allow deletion of pending logs
        if (!$timeLog->isPending()) {
            return redirect()->route('time-tracking.show', $timeLog->id)
                ->with('error', 'Only pending time logs can be deleted.');
        }

        $timeLog->delete();

        return redirect()->route('time-tracking.index')
            ->with('success', 'Time log deleted successfully.');
    }

    /**
     * Clock in a technician.
     */
    public function clockIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'technician_id' => 'required|exists:users,id',
            'work_order_id' => 'nullable|exists:work_orders,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = [
                'technician_id' => $request->technician_id,
                'log_type' => TimeLog::LOG_TYPE_CLOCK_IN,
                'log_time' => now(),
                'work_order_id' => $request->work_order_id,
                'appointment_id' => $request->appointment_id,
                'location' => $request->location,
                'notes' => $request->notes,
                'ip_address' => $request->ip(),
                'device_id' => 'web_' . $request->ip(),
            ];

            $timeLog = TimeLog::createLog($data);

            return response()->json([
                'success' => true,
                'message' => 'Clocked in successfully.',
                'data' => $timeLog
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Clock out a technician.
     */
    public function clockOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'technician_id' => 'required|exists:users,id',
            'work_order_id' => 'nullable|exists:work_orders,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = [
                'technician_id' => $request->technician_id,
                'log_type' => TimeLog::LOG_TYPE_CLOCK_OUT,
                'log_time' => now(),
                'work_order_id' => $request->work_order_id,
                'appointment_id' => $request->appointment_id,
                'location' => $request->location,
                'notes' => $request->notes,
                'ip_address' => $request->ip(),
                'device_id' => 'web_' . $request->ip(),
            ];

            $timeLog = TimeLog::createLog($data);

            return response()->json([
                'success' => true,
                'message' => 'Clocked out successfully.',
                'data' => $timeLog
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get technician's current status.
     */
    public function getStatus($technicianId)
    {
        $status = TimeLog::getTechnicianStatus($technicianId);
        $lastClockIn = TimeLog::getLastClockIn($technicianId);

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $status,
                'last_clock_in' => $lastClockIn,
                'current_time' => now()->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * Get technician's hours for a date.
     */
    public function getHours(Request $request, $technicianId)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $hours = TimeLog::getTotalHoursWorked($technicianId, $request->date);

        return response()->json([
            'success' => true,
            'data' => [
                'technician_id' => $technicianId,
                'date' => $request->date,
                'hours_worked' => $hours,
            ]
        ]);
    }

    /**
     * Approve a time log.
     */
    public function approve(Request $request, TimeLog $timeLog)
    {
        $validator = Validator::make($request->all(), [
            'approval_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $timeLog->approve(Auth::user(), $request->approval_notes);

            return redirect()->route('time-tracking.show', $timeLog->id)
                ->with('success', 'Time log approved successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Reject a time log.
     */
    public function reject(Request $request, TimeLog $timeLog)
    {
        $validator = Validator::make($request->all(), [
            'approval_notes' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $timeLog->reject(Auth::user(), $request->approval_notes);

            return redirect()->route('time-tracking.show', $timeLog->id)
                ->with('success', 'Time log rejected successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Bulk approve time logs.
     */
    public function bulkApprove(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'time_log_ids' => 'required|array',
            'time_log_ids.*' => 'exists:technician_time_logs,id',
            'approval_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $approved = 0;
        $failed = 0;

        foreach ($request->time_log_ids as $timeLogId) {
            try {
                $timeLog = TimeLog::findOrFail($timeLogId);
                
                if ($timeLog->isPending()) {
                    $timeLog->approve(Auth::user(), $request->approval_notes);
                    $approved++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $failed++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Approved {$approved} time logs. {$failed} failed.",
            'data' => [
                'approved' => $approved,
                'failed' => $failed,
            ]
        ]);
    }

    /**
     * Get technician's time log summary.
     */
    public function summary(Request $request, $technicianId = null)
    {
        $query = TimeLog::query();

        if ($technicianId) {
            $query->where('technician_id', $technicianId);
        }

        // Filter by date range
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        $query->whereBetween('log_time', [$startDate, $endDate]);

        // Get total hours worked
        $totalHours = 0;
        $timeLogs = $query->orderBy('log_time', 'asc')->get();

        $clockInTime = null;
        $breakStartTime = null;
        $lunchStartTime = null;

        foreach ($timeLogs as $log) {
            switch ($log->log_type) {
                case TimeLog::LOG_TYPE_CLOCK_IN:
                    $clockInTime = $log->log_time;
                    break;
                case TimeLog::LOG_TYPE_CLOCK_OUT:
                    if ($clockInTime) {
                        $totalHours += $clockInTime->diffInHours($log->log_time);
                        $clockInTime = null;
                    }
                    break;
                case TimeLog::LOG_TYPE_BREAK_START:
                    $breakStartTime = $log->log_time;
                    break;
                case TimeLog::LOG_TYPE_BREAK_END:
                    if ($breakStartTime) {
                        $totalHours -= $breakStartTime->diffInHours($log->log_time);
                        $breakStartTime = null;
                    }
                    break;
                case TimeLog::LOG_TYPE_LUNCH_START:
                    $lunchStartTime = $log->log_time;
                    break;
                case TimeLog::LOG_TYPE_LUNCH_END:
                    if ($lunchStartTime) {
                        $totalHours -= $lunchStartTime->diffInHours($log->log_time);
                        $lunchStartTime = null;
                    }
                    break;
            }
        }

        // Get statistics
        $stats = [
            'total_logs' => $timeLogs->count(),
            'clock_ins' => $timeLogs->where('log_type', TimeLog::LOG_TYPE_CLOCK_IN)->count(),
            'clock_outs' => $timeLogs->where('log_type', TimeLog::LOG_TYPE_CLOCK_OUT)->count(),
            'job_logs' => $timeLogs->whereIn('log_type', [TimeLog::LOG_TYPE_JOB_START, TimeLog::LOG_TYPE_JOB_END])->count(),
            'pending_logs' => $timeLogs->where('status', TimeLog::STATUS_PENDING)->count(),
            'approved_logs' => $timeLogs->where('status', TimeLog::STATUS_APPROVED)->count(),
            'total_hours' => round($totalHours, 2),
            'average_daily_hours' => round($totalHours / max(1, $timeLogs->groupBy(function($log) {
                return $log->log_time->format('Y-m-d');
            })->count()), 2),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        }

        $technician = $technicianId ? User::find($technicianId) : null;

        return view('time-tracking.summary', compact('stats', 'technician', 'startDate', 'endDate'));
    }

    /**
     * Export