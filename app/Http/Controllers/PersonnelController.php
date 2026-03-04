<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TimeLog;
use App\Models\PerformanceMetric;
use App\Models\TrainingRecord;
use App\Models\PartsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PersonnelController extends Controller
{
    /**
     * Display all personnel dashboard with filtering and sorting.
     */
    public function index(Request $request)
    {
        // Start query for all personnel (excluding customers)
        $query = User::whereNotIn('role', ['customer']);

        // Apply role filter if specified
        if ($request->has('role') && $request->role) {
            if ($request->role === 'office_staff') {
                // Office staff includes multiple roles
                $query->whereIn('role', ['admin', 'manager', 'service_advisor', 'office_staff']);
            } else {
                $query->where('role', $request->role);
            }
        }

        // Apply status filter if specified
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status === 'active');
        }

        // Apply search filter if specified
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('specialization', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        // Validate sort fields
        $validSortFields = ['name', 'email', 'role', 'hire_date', 'years_experience', 'created_at'];
        if (!in_array($sortBy, $validSortFields)) {
            $sortBy = 'name';
        }
        
        $query->orderBy($sortBy, $sortOrder);

        // Get paginated results
        $personnel = $query->paginate(20)->appends($request->except('page'));

        // Get statistics - using is_active instead of status
        $stats = [
            'total' => User::whereNotIn('role', ['customer'])->count(),
            'technicians' => User::where('role', 'technician')->count(),
            'office_staff' => User::whereIn('role', ['admin', 'manager', 'service_advisor', 'office_staff'])->count(),
            'executives' => User::where('role', 'executive')->count(),
            'active' => User::whereNotIn('role', ['customer'])->where('is_active', true)->count(),
            'inactive' => User::whereNotIn('role', ['customer'])->where('is_active', false)->count(),
        ];

        // Get departments for filtering - check if Department model exists
        $departments = [];
        if (class_exists('App\\Models\\Department')) {
            $departments = \App\Models\Department::all();
        }

        // Get all available roles for filtering
        $availableRoles = [
            'technician' => 'Technician',
            'service_advisor' => 'Service Advisor',
            'manager' => 'Manager',
            'admin' => 'Administrator',
            'office_staff' => 'Office Staff',
            'executive' => 'Executive',
        ];

        return view('personnel.index', compact('personnel', 'stats', 'departments', 'availableRoles'));
    }

    /**
     * Display technicians only.
     */
    // Category-specific methods removed - now using single personnel page with filtering

    /**
     * Show the form for creating new personnel.
     */
    public function create()
    {
        $roles = [
            'technician' => 'Technician',
            'service_advisor' => 'Service Advisor',
            'manager' => 'Manager',
            'admin' => 'Administrator',
            'office_staff' => 'Office Staff',
            'executive' => 'Executive',
        ];

        return view('personnel.create', compact('roles'));
    }

    /**
     * Store a newly created personnel in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|string|max:50',
            'specialization' => 'nullable|string|max:500',
            'years_experience' => 'nullable|integer|min:0',
            'hire_date' => 'nullable|date',
            'is_active' => 'required|boolean',
            'notes' => 'nullable|string',
            'additional_roles' => 'nullable|array',
            'additional_roles.*' => 'string|max:50',
        ]);

        // Prepare notes with additional roles if any
        $notes = $validated['notes'] ?? '';
        if (!empty($validated['additional_roles'])) {
            $additionalRolesText = 'Additional Roles: ' . implode(', ', array_map(function($roleValue) {
                // Convert role value to readable name
                $roleMap = [
                    'technician' => 'Technician',
                    'service_advisor' => 'Service Advisor',
                    'manager' => 'Manager',
                    'admin' => 'Administrator',
                    'office_staff' => 'Office Staff',
                    'executive' => 'Executive'
                ];
                
                return $roleMap[$roleValue] ?? ucfirst(str_replace('_', ' ', $roleValue));
            }, $validated['additional_roles']));
            
            $notes = $notes ? $notes . "\n\n" . $additionalRolesText : $additionalRolesText;
        }
        
        // Create the user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'specialization' => $validated['specialization'] ?? null,
            'years_experience' => $validated['years_experience'] ?? 0,
            'hire_date' => $validated['hire_date'] ?? now(),
            'is_active' => $validated['is_active'],
            'notes' => $notes,
            'password' => Hash::make('password123'), // Default password
        ]);

        return redirect()->route('personnel.show', $user)
            ->with('success', 'Personnel created successfully. Default password: password123');
    }

    /**
     * Display the specified personnel.
     */
    public function show(User $user)
    {
        // Load related data based on role
        if ($user->isTechnician()) {
            $user->load([
                'timeLogs' => function ($query) {
                    $query->orderBy('clock_in', 'desc')->limit(10);
                },
                'performanceMetrics' => function ($query) {
                    $query->orderBy('created_at', 'desc')->limit(10);
                },
                'trainingRecords' => function ($query) {
                    $query->with('trainingModule')->orderBy('created_at', 'desc')->limit(10);
                },
            ]);
        }

        return view('personnel.show', compact('user'));
    }

    /**
     * Show the form for editing the specified personnel.
     */
    public function edit(User $user)
    {
        $roles = [
            'technician' => 'Technician',
            'service_advisor' => 'Service Advisor',
            'manager' => 'Manager',
            'admin' => 'Administrator',
            'office_staff' => 'Office Staff',
            'executive' => 'Executive',
        ];

        return view('personnel.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified personnel in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|string|max:50',
            'specialization' => 'nullable|string|max:500',
            'years_experience' => 'nullable|integer|min:0',
            'hire_date' => 'nullable|date',
            'is_active' => 'required|boolean',
            'notes' => 'nullable|string',
            'additional_roles' => 'nullable|array',
            'additional_roles.*' => 'string|max:50',
        ]);

        // Prepare notes with additional roles if any
        $notes = $validated['notes'] ?? '';
        if (!empty($validated['additional_roles'])) {
            $additionalRolesText = 'Additional Roles: ' . implode(', ', array_map(function($roleValue) {
                // Convert role value to readable name
                $roleMap = [
                    'technician' => 'Technician',
                    'service_advisor' => 'Service Advisor',
                    'manager' => 'Manager',
                    'admin' => 'Administrator',
                    'office_staff' => 'Office Staff',
                    'executive' => 'Executive'
                ];
                
                return $roleMap[$roleValue] ?? ucfirst(str_replace('_', ' ', $roleValue));
            }, $validated['additional_roles']));
            
            $notes = $notes ? $notes . "\n\n" . $additionalRolesText : $additionalRolesText;
        }
        
        $validated['notes'] = $notes;

        $user->update($validated);

        return redirect()->route('personnel.show', $user)
            ->with('success', 'Personnel updated successfully');
    }

    /**
     * Remove the specified personnel from storage.
     */
    public function destroy(User $user)
    {
        // Don't allow deletion of the last admin
        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return redirect()->route('personnel.index')
                ->with('error', 'Cannot delete the last administrator');
        }

        $user->delete();

        return redirect()->route('personnel.index')
            ->with('success', 'Personnel deleted successfully');
    }

    /**
     * Assign a new role to personnel.
     */
    public function assignRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|string|max:50',
        ]);

        $user->update(['role' => $validated['role']]);

        return redirect()->route('personnel.show', $user)
            ->with('success', 'Role assigned successfully');
    }

    /**
     * Update department for personnel.
     * Note: Department functionality is not implemented in this version.
     * Uncomment and implement when departments table and model are created.
     */
    /*
    public function updateDepartment(Request $request, User $user)
    {
        $validated = $request->validate([
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $user->update(['department_id' => $validated['department_id']]);

        return redirect()->route('personnel.show', $user)
            ->with('success', 'Department updated successfully');
    }
    */

    /**
     * Display performance metrics for personnel.
     */
    public function performance(User $user)
    {
        if (!$user->isTechnician()) {
            return redirect()->route('personnel.show', $user)
                ->with('error', 'Performance metrics are only available for technicians');
        }

        $performanceMetrics = PerformanceMetric::where('technician_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'avg_efficiency' => PerformanceMetric::where('technician_id', $user->id)->avg('efficiency_score') ?? 0,
            'avg_quality' => PerformanceMetric::where('technician_id', $user->id)->avg('quality_score') ?? 0,
            'total_jobs' => PerformanceMetric::where('technician_id', $user->id)->count(),
        ];

        return view('personnel.performance', compact('user', 'performanceMetrics', 'stats'));
    }

    /**
     * Export personnel data.
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'all');
        
        switch ($type) {
            case 'technicians':
                $personnel = User::where('role', 'technician')->get();
                $filename = 'technicians_' . date('Y-m-d') . '.csv';
                break;
            case 'office-staff':
                $personnel = User::whereIn('role', ['admin', 'manager', 'service_advisor', 'office_staff'])->get();
                $filename = 'office_staff_' . date('Y-m-d') . '.csv';
                break;
            case 'executives':
                $personnel = User::where('role', 'executive')->get();
                $filename = 'executives_' . date('Y-m-d') . '.csv';
                break;
            default:
                $personnel = User::whereNotIn('role', ['customer'])->get();
                $filename = 'all_personnel_' . date('Y-m-d') . '.csv';
                break;
        }

        // In a real implementation, you would generate a CSV or Excel file
        // For now, we'll just return a success message
        return redirect()->route('personnel.index')
            ->with('success', 'Export file generated: ' . $filename . ' (This is a demo - in production, a file would be downloaded)');
    }
}