<?php

namespace App\Http\Controllers;

use App\Models\TrainingModule;
use App\Models\TrainingRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrainingController extends Controller
{
    /**
     * Display a listing of training modules.
     */
    public function index(Request $request)
    {
        $query = TrainingModule::active();

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Filter by difficulty
        if ($request->has('difficulty') && $request->difficulty) {
            $query->where('difficulty_level', $request->difficulty);
        }

        // Filter by search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('module_code', 'like', '%' . $request->search . '%');
            });
        }

        $modules = $query->paginate(12);

        return view('training.index', [
            'modules' => $modules,
            'categories' => TrainingModule::getCategories(),
            'difficultyLevels' => TrainingModule::getDifficultyLevels(),
        ]);
    }

    /**
     * Display the specified training module.
     */
    public function show(TrainingModule $module)
    {
        // Get user's training record for this module if exists
        $userRecord = null;
        if (Auth::check()) {
            $userRecord = TrainingRecord::where('technician_id', Auth::id())
                ->where('training_module_id', $module->id)
                ->first();
        }

        // Get statistics for this module
        $completionRate = $module->completion_rate;
        $averageScore = $module->average_score;

        return view('training.show', [
            'module' => $module,
            'userRecord' => $userRecord,
            'completionRate' => $completionRate,
            'averageScore' => $averageScore,
        ]);
    }

    /**
     * Start a training module.
     */
    public function start(TrainingModule $module)
    {
        $user = Auth::user();

        // Check if user already has a record for this module
        $record = TrainingRecord::where('technician_id', $user->id)
            ->where('training_module_id', $module->id)
            ->first();

        if (!$record) {
            // Create new training record
            $record = TrainingRecord::create([
                'technician_id' => $user->id,
                'training_module_id' => $module->id,
                'status' => 'assigned',
            ]);
        }

        // Mark as started
        $record->markAsStarted();

        return redirect()->route('training.show', $module)
            ->with('success', 'Training module started successfully.');
    }

    /**
     * Complete a training module.
     */
    public function complete(Request $request, TrainingModule $module)
    {
        $request->validate([
            'score' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();

        // Get the training record
        $record = TrainingRecord::where('technician_id', $user->id)
            ->where('training_module_id', $module->id)
            ->firstOrFail();

        // Mark as completed
        $record->markAsCompleted(
            $request->score,
            $request->notes
        );

        return redirect()->route('training.show', $module)
            ->with('success', 'Training module completed successfully.');
    }

    /**
     * Display user's training progress.
     */
    public function progress(Request $request)
    {
        $user = Auth::user();

        $query = TrainingRecord::where('technician_id', $user->id)
            ->with('trainingModule');

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $records = $query->paginate(10);

        // Calculate statistics
        $totalAssigned = TrainingRecord::where('technician_id', $user->id)->count();
        $totalCompleted = TrainingRecord::where('technician_id', $user->id)->completed()->count();
        $totalInProgress = TrainingRecord::where('technician_id', $user->id)->inProgress()->count();
        
        $completionRate = $totalAssigned > 0 ? round(($totalCompleted / $totalAssigned) * 100, 1) : 0;
        $averageScore = TrainingRecord::where('technician_id', $user->id)
            ->completed()
            ->whereNotNull('score')
            ->avg('score');

        return view('training.progress', [
            'records' => $records,
            'totalAssigned' => $totalAssigned,
            'totalCompleted' => $totalCompleted,
            'totalInProgress' => $totalInProgress,
            'completionRate' => $completionRate,
            'averageScore' => $averageScore,
        ]);
    }

    /**
     * Display training dashboard for managers.
     */
    public function dashboard()
    {
        // Only managers and admins can access
        if (!Auth::user()->isAdmin() && !Auth::user()->isManager()) {
            abort(403);
        }

        // Get all technicians
        $technicians = User::technicians()->active()->get();

        // Get training statistics
        $totalModules = TrainingModule::active()->count();
        $totalTrainingRecords = TrainingRecord::count();
        $completedTraining = TrainingRecord::completed()->count();
        $inProgressTraining = TrainingRecord::inProgress()->count();

        // Get module completion rates
        $modules = TrainingModule::active()
            ->withCount(['trainingRecords', 'trainingRecords as completed_records_count' => function ($query) {
                $query->where('status', 'completed');
            }])
            ->get()
            ->map(function ($module) {
                $module->completion_rate = $module->training_records_count > 0 
                    ? round(($module->completed_records_count / $module->training_records_count) * 100, 1)
                    : 0;
                return $module;
            })
            ->sortByDesc('completion_rate');

        // Get technician training progress
        $technicianProgress = $technicians->map(function ($technician) {
            $assigned = TrainingRecord::where('technician_id', $technician->id)->count();
            $completed = TrainingRecord::where('technician_id', $technician->id)->completed()->count();
            
            $technician->training_progress = $assigned > 0 ? round(($completed / $assigned) * 100, 1) : 0;
            $technician->assigned_count = $assigned;
            $technician->completed_count = $completed;
            
            return $technician;
        })->sortByDesc('training_progress');

        return view('training.dashboard', [
            'totalModules' => $totalModules,
            'totalTrainingRecords' => $totalTrainingRecords,
            'completedTraining' => $completedTraining,
            'inProgressTraining' => $inProgressTraining,
            'modules' => $modules,
            'technicianProgress' => $technicianProgress,
        ]);
    }

    /**
     * Assign training to a technician.
     */
    public function assign(Request $request)
    {
        // Only managers and admins can assign training
        if (!Auth::user()->isAdmin() && !Auth::user()->isManager()) {
            abort(403);
        }

        $request->validate([
            'technician_id' => 'required|exists:users,id',
            'training_module_id' => 'required|exists:training_modules,id',
        ]);

        // Check if already assigned
        $existing = TrainingRecord::where('technician_id', $request->technician_id)
            ->where('training_module_id', $request->training_module_id)
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Training already assigned to this technician.');
        }

        // Create training record
        TrainingRecord::create([
            'technician_id' => $request->technician_id,
            'training_module_id' => $request->training_module_id,
            'status' => 'assigned',
        ]);

        return redirect()->back()->with('success', 'Training assigned successfully.');
    }
}