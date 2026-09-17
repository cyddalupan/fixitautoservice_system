<?php

namespace App\Http\Controllers;

use App\Models\BlueprintService;
use Illuminate\Http\Request;

/**
 * Blueprint /admin/services catalog CRUD.
 *
 * Source of truth: Fixit Blueprint card + fixit-remake-feature-plan.md.
 * Simple `services` table (name, description, default_price decimal,
 * category, is_active). No brand/model break-out.
 */
class AdminServiceController extends Controller
{
    public function index()
    {
        $services = BlueprintService::orderBy('name')->get();
        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        BlueprintService::create($data + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.services.index')
            ->with('success', 'Service created.');
    }

    public function edit(BlueprintService $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, BlueprintService $service)
    {
        $data = $this->validateData($request);

        $service->update($data + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.services.index')
            ->with('success', 'Service updated.');
    }

    public function destroy(BlueprintService $service)
    {
        $service->delete();

        return redirect()->route('admin.services.index')
            ->with('success', 'Service deleted.');
    }

    public function toggle(BlueprintService $service)
    {
        $service->update(['is_active' => ! $service->is_active]);

        return redirect()->route('admin.services.index')
            ->with('success', 'Service ' . ($service->is_active ? 'enabled' : 'disabled') . '.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'default_price' => ['required', 'numeric', 'min:0'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
