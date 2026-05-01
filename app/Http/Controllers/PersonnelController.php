<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Appointment;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Traits\HandlesCroppedImage;

class PersonnelController extends Controller
{
    protected array $validRoles;

    use HandlesCroppedImage;

    public function __construct()
    {
        $this->validRoles = array_keys(User::roleLabels());
    }

    /**
     * Display a listing of personnel.
     */
    public function index(Request $request)
    {
        $query = User::whereNotIn('role', ['customer']);

        // Search/filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role_filter')) {
            $roleFilter = $request->role_filter;
            // Search primary role OR JSON roles column
            $query->where(function ($q) use ($roleFilter) {
                $q->where('role', $roleFilter)
                  ->orWhere('roles', 'like', "%\"{$roleFilter}\"%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active' ? 1 : 0);
        }

        // Sorting
        $sortBy = $request->sort_by ?? 'name';
        $sortOrder = $request->sort_order ?? 'asc';
        $allowedSorts = ['name', 'role', 'hire_date', 'years_experience', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) $sortBy = 'name';
        if (!in_array($sortOrder, ['asc', 'desc'])) $sortOrder = 'asc';
        $query->orderBy($sortBy, $sortOrder);

        $personnel = $query->paginate(20)->withQueryString();

        $availableRoles = User::roleLabels();

        // Stats
        $stats = [
            'total' => User::whereNotIn('role', ['customer'])->count(),
            'technicians' => User::whereNotIn('role', ['customer'])->where('role', 'technician')->count(),
            'office_staff' => User::whereNotIn('role', ['customer'])->whereIn('role', ['office_staff', 'service_advisor'])->count(),
            'executives' => User::whereNotIn('role', ['customer'])->whereIn('role', ['super_admin', 'admin', 'executive', 'manager'])->count(),
        ];

        return view('personnel.index', compact('personnel', 'availableRoles', 'stats'));
    }

    /**
     * Show the form for creating new personnel.
     */
    public function create()
    {
        $availableRoles = User::roleLabels();
        return view('personnel.create', compact('availableRoles'));
    }

    /**
     * Store newly created personnel.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|max:255|unique:users,email',
            'phone'           => 'nullable|string|max:50',
            'address'         => 'nullable|string|max:500',
            'role'            => 'required|string|in:' . implode(',', $this->validRoles),
            'roles'           => 'nullable|string',
            'password'        => 'required|string|min:8',
            'is_active'       => 'boolean',
            'employee_id'     => 'nullable|string|max:50',
            'hire_date'       => 'nullable|date',
            'employment_type' => 'nullable|string|max:50',
            'specialization'  => 'nullable|string|max:255',
            'skills'          => 'nullable|string',
            'shift_schedule'  => 'nullable|string|max:100',
            'hourly_rate'     => 'nullable|numeric|min:0',
            'notes'           => 'nullable|string|max:2000',
            'profile_photo'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'cropped_image'   => 'nullable|string',
        ]);

        // Parse comma-separated roles string into array, validate each
        $rolesArray = [];
        if (!empty($validated['roles'])) {
            $parts = explode(',', $validated['roles']);
            foreach ($parts as $r) {
                $r = trim($r);
                if (!empty($r) && in_array($r, $this->validRoles)) {
                    $rolesArray[] = $r;
                }
            }
        }

        $data = [
            'name'            => $validated['name'],
            'email'           => $validated['email'],
            'password'        => Hash::make($validated['password']),
            'role'            => $validated['role'],
            'roles'           => $rolesArray,
            'phone'           => $validated['phone'] ?? null,
            'address'         => $validated['address'] ?? null,
            'is_active'       => $request->boolean('is_active', true),
            'employee_id'     => $validated['employee_id'] ?? null,
            'hire_date'       => $validated['hire_date'] ?? null,
            'employment_type' => $validated['employment_type'] ?? null,
            'specialization'  => $validated['specialization'] ?? null,
            'skills'          => $validated['skills'] ?? null,
            'shift_schedule'  => $validated['shift_schedule'] ?? null,
            'hourly_rate'     => $validated['hourly_rate'] ?? null,
            'notes'           => $validated['notes'] ?? null,
        ];

        // Handle profile photo (cropped or raw upload)
        $path = $this->saveCroppedImage($request, 'cropped_image', 'profile_photo', 'profile-photos', 300, 85);
        if ($path) {
            $data['profile_photo_path'] = $path;
        }

        $user = User::create($data);

        return redirect()->route('personnel.index')
            ->with('success', "Personnel {$user->name} created successfully.");
    }

    /**
     * Display the specified personnel.
     */
    public function show($id)
    {
        $personnel = User::whereNotIn('role', ['customer'])->findOrFail($id);

        // Get assigned appointments
        $appointments = Appointment::where('assigned_technician_id', $personnel->id)
            ->orWhere('service_advisor_id', $personnel->id)
            ->orderBy('appointment_date', 'desc')
            ->take(10)
            ->get();

        // Get work orders
        $workOrders = WorkOrder::where('technician_id', $personnel->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $availableRoles = User::roleLabels();

        return view('personnel.show', compact('personnel', 'appointments', 'workOrders', 'availableRoles'));
    }

    /**
     * Show the form for editing personnel.
     */
    public function edit($id)
    {
        $personnel = User::whereNotIn('role', ['customer'])->findOrFail($id);
        $availableRoles = User::roleLabels();
        return view('personnel.edit', compact('personnel', 'availableRoles'));
    }

    /**
     * Update the specified personnel.
     */
    public function update(Request $request, $id)
    {
        $personnel = User::whereNotIn('role', ['customer'])->findOrFail($id);

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|max:255|unique:users,email,' . $personnel->id,
            'phone'           => 'nullable|string|max:50',
            'address'         => 'nullable|string|max:500',
            'role'            => 'required|string|in:' . implode(',', $this->validRoles),
            'roles'           => 'nullable|string',
            'password'        => 'nullable|string|min:8',
            'is_active'       => 'boolean',
            'employee_id'     => 'nullable|string|max:50',
            'hire_date'       => 'nullable|date',
            'employment_type' => 'nullable|string|max:50',
            'specialization'  => 'nullable|string|max:255',
            'skills'          => 'nullable|string',
            'shift_schedule'  => 'nullable|string|max:100',
            'hourly_rate'     => 'nullable|numeric|min:0',
            'notes'           => 'nullable|string|max:2000',
            'profile_photo'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'cropped_image'   => 'nullable|string',
            'remove_photo'    => 'boolean',
        ]);

        // Parse comma-separated roles string into array, validate each
        $rolesArray = [];
        if (!empty($validated['roles'])) {
            $parts = explode(',', $validated['roles']);
            foreach ($parts as $r) {
                $r = trim($r);
                if (!empty($r) && in_array($r, $this->validRoles)) {
                    $rolesArray[] = $r;
                }
            }
        }

        $data = [
            'name'            => $validated['name'],
            'email'           => $validated['email'],
            'role'            => $validated['role'],
            'roles'           => $rolesArray,
            'phone'           => $validated['phone'] ?? null,
            'address'         => $validated['address'] ?? null,
            'is_active'       => $request->boolean('is_active', true),
            'employee_id'     => $validated['employee_id'] ?? null,
            'hire_date'       => $validated['hire_date'] ?? null,
            'employment_type' => $validated['employment_type'] ?? null,
            'specialization'  => $validated['specialization'] ?? null,
            'skills'          => $validated['skills'] ?? null,
            'shift_schedule'  => $validated['shift_schedule'] ?? null,
            'hourly_rate'     => $validated['hourly_rate'] ?? null,
            'notes'           => $validated['notes'] ?? null,
        ];

        // Handle password update
        if ($request->filled('password')) {
            $data['password'] = Hash::make($validated['password']);
        }

        // Handle profile photo (cropped or raw upload)
        if ($request->filled('cropped_image') || $request->hasFile('profile_photo')) {
            // Delete old photo
            $this->deleteStoredImage($personnel->profile_photo_path);
            $path = $this->saveCroppedImage($request, 'cropped_image', 'profile_photo', 'profile-photos', 300, 85);
            if ($path) {
                $data['profile_photo_path'] = $path;
            }
        }

        // Handle photo removal
        if ($request->boolean('remove_photo')) {
            if ($personnel->profile_photo_path) {
                Storage::disk('public')->delete($personnel->profile_photo_path);
            }
            $data['profile_photo_path'] = null;
        }

        $personnel->update($data);

        return redirect()->route('personnel.show', $personnel->id)
            ->with('success', "Personnel {$personnel->name} updated successfully.");
    }

    /**
     * Remove the specified personnel from storage.
     */
    public function destroy($id)
    {
        $personnel = User::whereNotIn('role', ['customer'])->findOrFail($id);

        // Don't delete super_admin records
        if ($personnel->role === 'super_admin') {
            return redirect()->route('personnel.index')
                ->with('error', 'Super Admin accounts cannot be deleted.');
        }

        // Delete profile photo if exists
        if ($personnel->profile_photo_path) {
            Storage::disk('public')->delete($personnel->profile_photo_path);
        }

        $name = $personnel->name;
        $personnel->delete();

        return redirect()->route('personnel.index')
            ->with('success', "Personnel {$name} deleted successfully.");
    }

    /**
     * Get initial characters for avatar fallback.
     */
    public static function getInitials(?string $name): string
    {
        if (empty($name)) return '?';
        $words = explode(' ', trim($name));
        $initials = '';
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper(mb_substr($word, 0, 1));
            }
        }
        return substr($initials, 0, 2);
    }

    /**
     * Get color for initials avatar background based on name.
     */
    public static function getInitialsColor(?string $name): string
    {
        $colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#5a5c69', '#6f42c1', '#fd7e14', '#20c997', '#e83e8c'];
        if (empty($name)) return $colors[0];
        $index = crc32($name) % count($colors);
        return $colors[abs($index)];
    }

    /**
     * Export personnel to CSV.
     */
    public function export()
    {
        $personnel = User::whereNotIn('role', ['customer'])->get();

        $csv = "Name,Email,Phone,Role,Status,Employee ID,Hire Date\n";
        foreach ($personnel as $p) {
            $roles = implode(', ', array_map(fn($r) => User::roleLabels()[$r] ?? $r, $p->all_roles));
            $csv .= '"' . $p->name . '","' . $p->email . '","' . ($p->phone ?? '') . '","' . $roles . '",' . ($p->is_active ? 'Active' : 'Inactive') . ',"' . ($p->employee_id ?? '') . '","' . ($p->hire_date ? $p->hire_date->format('Y-m-d') : '') . '"' . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="personnel_export_' . now()->format('Ymd_His') . '.csv"',
        ]);
    }

    /**
     * Assign additional role to a user.
     */
    public function assignRole(Request $request, $id)
    {
        $personnel = User::whereNotIn('role', ['customer'])->findOrFail($id);

        $request->validate([
            'role' => 'required|string|in:' . implode(',', $this->validRoles),
        ]);

        $currentRoles = $personnel->roles ?? [];
        if (!is_array($currentRoles)) {
            $currentRoles = [];
        }

        if (!in_array($request->role, $currentRoles)) {
            $currentRoles[] = $request->role;
            $personnel->update(['roles' => $currentRoles]);
        }

        return redirect()->route('personnel.show', $personnel)
            ->with('success', "Role assigned to {$personnel->name}.");
    }

    /**
     * View personnel performance summary.
     */
    public function performance($id)
    {
        $personnel = User::whereNotIn('role', ['customer'])->findOrFail($id);

        $appointmentCount = Appointment::where('technician_id', $personnel->id)->count();
        $workOrderCount = WorkOrder::where('technician_id', $personnel->id)->count();

        return view('personnel.performance', compact('personnel', 'appointmentCount', 'workOrderCount'));
    }

    /**
     * API: Get available roles.
     */
    public function roles()
    {
        return response()->json(User::roleLabels());
    }
}
