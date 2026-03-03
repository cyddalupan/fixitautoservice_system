<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TimeLog;
use App\Models\PerformanceMetric;
use App\Models\TrainingRecord;
use App\Models\PartsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TechnicianController extends Controller
{
    /**
     * Display technician overview dashboard.
     */
    public function overview()
    {
        $user = Auth::user();

        // Only technicians can access their overview
        if (!$user->isTechnician()) {
            abort(403);
        }

        // Get time tracking status
        $currentTimeLog = TimeLog::where('technician_id', $user->id)
            ->where('status', TimeLog::STATUS_CLOCKED_IN)
            ->first();

        // Get today's time logs
        $todayLogs = TimeLog::where('technician_id', $user->id)
            ->whereDate('clock_in', today())
            ->orderBy('clock_in', 'desc')
            ->get();

        // Calculate today's hours
        $todayHours = 0;
        foreach ($todayLogs as $log) {
            if ($log->clock_out) {
                $todayHours += $log->clock_in->diffInHours($log->clock_out);
            }
        }

        // Get this week's hours
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        
        $weekLogs = TimeLog::where('technician_id', $user->id)
            ->whereBetween('clock_in', [$weekStart, $weekEnd])
            ->get();

        $weekHours = 0;
        foreach ($weekLogs as $log) {
            if ($log->clock_out) {
                $weekHours += $log->clock_in->diffInHours($log->clock_out);
            }
        }

        // Get performance metrics
        $performanceMetrics = PerformanceMetric::where('technician_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $latestPerformance = PerformanceMetric::where('technician_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // Get training progress
        $trainingRecords = TrainingRecord::where('technician_id', $user->id)
            ->with('trainingModule')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $trainingStats = [
            'total' => TrainingRecord::where('technician_id', $user->id)->count(),
            'completed' => TrainingRecord::where('technician_id', $user->id)->completed()->count(),
            'in_progress' => TrainingRecord::where('technician_id', $user->id)->inProgress()->count(),
        ];

        // Get parts requests
        $partsRequests = PartsRequest::where('technician_id', $user->id)
            ->with(['workOrder', 'vehicle'])
            ->orderBy('requested_at', 'desc')
            ->limit(5)
            ->get();

        $partsRequestStats = [
            'total' => PartsRequest::where('technician_id', $user->id)->count(),
            'pending' => PartsRequest::where('technician_id', $user->id)->pending()->count(),
            'approved' => PartsRequest::where('technician_id', $user->id)->approved()->count(),
            'installed' => PartsRequest::where('technician_id', $user->id)->installed()->count(),
        ];

        // Get recent work orders assigned to this technician
        $recentWorkOrders = $user->technicianServiceRecords()
            ->with(['vehicle', 'customer'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('technician.overview', [
            'user' => $user,
            'currentTimeLog' => $currentTimeLog,
            'todayHours' => $todayHours,
            'weekHours' => $weekHours,
            'todayLogs' => $todayLogs,
            'performanceMetrics' => $performanceMetrics,
            'latestPerformance' => $latestPerformance,
            'trainingRecords' => $trainingRecords,
            'trainingStats' => $trainingStats,
            'partsRequests' => $partsRequests,
            'partsRequestStats' => $partsRequestStats,
            'recentWorkOrders' => $recentWorkOrders,
        ]);
    }

    /**
     * Display technician profile.
     */
    public function profile()
    {
        $user = Auth::user();

        // Only technicians can access their profile
        if (!$user->isTechnician()) {
            abort(403);
        }

        // Get skills and certifications from user model
        $skills = $user->skills ?? [];
        $certifications = $user->certifications ?? [];

        // Get training completion rate
        $totalTraining = TrainingRecord::where('technician_id', $user->id)->count();
        $completedTraining = TrainingRecord::where('technician_id', $user->id)->completed()->count();
        $trainingCompletionRate = $totalTraining > 0 ? round(($completedTraining / $totalTraining) * 100, 1) : 0;

        // Get average performance score
        $averagePerformance = PerformanceMetric::where('technician_id', $user->id)
            ->whereNotNull('overall_score')
            ->avg('overall_score');

        // Get parts request success rate
        $totalPartsRequests = PartsRequest::where('technician_id', $user->id)->count();
        $installedPartsRequests = PartsRequest::where('technician_id', $user->id)->installed()->count();
        $partsRequestSuccessRate = $totalPartsRequests > 0 ? round(($installedPartsRequests / $totalPartsRequests) * 100, 1) : 0;

        return view('technician.profile', [
            'user' => $user,
            'skills' => $skills,
            'certifications' => $certifications,
            'trainingCompletionRate' => $trainingCompletionRate,
            'averagePerformance' => $averagePerformance,
            'partsRequestSuccessRate' => $partsRequestSuccessRate,
        ]);
    }

    /**
     * Update technician profile.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // Only technicians can update their profile
        if (!$user->isTechnician()) {
            abort(403);
        }

        $request->validate([
            'specialization' => 'nullable|string|max:255',
            'years_experience' => 'nullable|integer|min:0|max:50',
            'shift_schedule' => 'nullable|string|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:255',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:100',
            'certifications' => 'nullable|array',
            'certifications.*' => 'string|max:100',
        ]);

        $user->update([
            'specialization' => $request->specialization,
            'years_experience' => $request->years_experience,
            'shift_schedule' => $request->shift_schedule,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'skills' => $request->skills ?? [],
            'certifications' => $request->certifications ?? [],
        ]);

        return redirect()->route('technician.profile')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Display technician statistics.
     */
    public function statistics()
    {
        $user = Auth::user();

        // Only technicians can access their statistics
        if (!$user->isTechnician()) {
            abort(403);
        }

        // Get monthly hours
        $monthlyHours = TimeLog::selectRaw('
            DATE_FORMAT(clock_in, "%Y-%m") as month,
            SUM(TIMESTAMPDIFF(HOUR, clock_in, COALESCE(clock_out, NOW()))) as total_hours
        ')
        ->where('technician_id', $user->id)
        ->whereNotNull('clock_in')
        ->where('status', '!=', TimeLog::STATUS_REJECTED)
        ->groupBy('month')
        ->orderBy('month', 'desc')
        ->limit(6)
        ->get();

        // Get performance trend
        $performanceTrend = PerformanceMetric::selectRaw('
            DATE_FORMAT(created_at, "%Y-%m") as month,
            AVG(overall_score) as avg_score
        ')
        ->where('technician_id', $user->id)
        ->whereNotNull('overall_score')
        ->groupBy('month')
        ->orderBy('month', 'desc')
        ->limit(6)
        ->get();

        // Get training completion by category
        $trainingByCategory = TrainingRecord::selectRaw('
            tm.category,
            COUNT(*) as total,
            SUM(CASE WHEN tr.status = "completed" THEN 1 ELSE 0 END) as completed
        ')
        ->from('technician_training_records as tr')
        ->join('training_modules as tm', 'tr.training_module_id', '=', 'tm.id')
        ->where('tr.technician_id', $user->id)
        ->groupBy('tm.category')
        ->get();

        // Get parts request status distribution
        $partsRequestDistribution = PartsRequest::selectRaw('
            status,
            COUNT(*) as count
        ')
        ->where('technician_id', $user->id)
        ->groupBy('status')
        ->get();

        return view('technician.statistics', [
            'user' => $user,
            'monthlyHours' => $monthlyHours,
            'performanceTrend' => $performanceTrend,
            'trainingByCategory' => $trainingByCategory,
            'partsRequestDistribution' => $partsRequestDistribution,
        ]);
    }

    /**
     * Display a listing of technicians (for admin management).
     */
    public function index()
    {
        // Only admin can access technician management
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $technicians = User::technicians()->paginate(10);
        
        return view('technicians.index', compact('technicians'));
    }

    /**
     * Show the form for creating a new technician.
     */
    public function create()
    {
        // Only admin can create technicians
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        return view('technicians.create');
    }

    /**
     * Store a newly created technician in storage.
     */
    public function store(Request $request)
    {
        // Only admin can create technicians
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'specialization' => 'nullable|string|max:255',
            'years_experience' => 'nullable|integer|min:0|max:50',
            'shift_schedule' => 'nullable|string|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:100',
            'certifications' => 'nullable|array',
            'certifications.*' => 'string|max:100',
        ]);

        // Create technician user with default password
        $technician = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => 'technician',
            'password' => bcrypt('technician123'), // Default password, should be changed on first login
            'specialization' => $request->specialization,
            'years_experience' => $request->years_experience,
            'shift_schedule' => $request->shift_schedule,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'skills' => $request->skills ?? [],
            'certifications' => $request->certifications ?? [],
        ]);

        return redirect()->route('technicians.index')
            ->with('success', 'Technician created successfully. Default password: technician123');
    }

    /**
     * Display the specified technician.
     */
    public function show(User $user)
    {
        // Only admin can view technician details
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        // Ensure the user is a technician
        if (!$user->isTechnician()) {
            abort(404);
        }

        return view('technicians.show', compact('user'));
    }

    /**
     * Show the form for editing the specified technician.
     */
    public function edit(User $user)
    {
        // Only admin can edit technicians
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        // Ensure the user is a technician
        if (!$user->isTechnician()) {
            abort(404);
        }

        return view('technicians.edit', compact('user'));
    }

    /**
     * Update the specified technician in storage.
     */
    public function update(Request $request, User $user)
    {
        // Only admin can update technicians
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        // Ensure the user is a technician
        if (!$user->isTechnician()) {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'specialization' => 'nullable|string|max:255',
            'years_experience' => 'nullable|integer|min:0|max:50',
            'shift_schedule' => 'nullable|string|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:100',
            'certifications' => 'nullable|array',
            'certifications.*' => 'string|max:100',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'specialization' => $request->specialization,
            'years_experience' => $request->years_experience,
            'shift_schedule' => $request->shift_schedule,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'skills' => $request->skills ?? [],
            'certifications' => $request->certifications ?? [],
        ]);

        return redirect()->route('technicians.index')
            ->with('success', 'Technician updated successfully.');
    }

    /**
     * Remove the specified technician from storage.
     */
    public function destroy(User $user)
    {
        // Only admin can delete technicians
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        // Ensure the user is a technician
        if (!$user->isTechnician()) {
            abort(404);
        }

        $user->delete();

        return redirect()->route('technicians.index')
            ->with('success', 'Technician deleted successfully.');
    }
}