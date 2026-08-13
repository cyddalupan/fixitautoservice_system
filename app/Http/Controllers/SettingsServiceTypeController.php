<?php

namespace App\Http\Controllers;

use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Service Types CRUD — lives under the Settings module
 * (/settings/service-types) so the shop can adjust the services list
 * quickly without editing config files or migrations.
 *
 * Edits the `service_types` table, which is the single source of truth
 * for the booking form, the admin create-page selector and API
 * validation. config/service-types.php remains as a fallback only.
 */
class SettingsServiceTypeController extends Controller
{
    public function index()
    {
        $serviceTypes = ServiceType::orderBy('sort_order')->get();

        return view('settings.service-types.index', compact('serviceTypes'));
    }

    public function create()
    {
        return view('settings.service-types.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        ServiceType::create($data);

        return redirect()
            ->route('settings.service-types.index')
            ->with('success', 'Service type created.');
    }

    public function edit(ServiceType $serviceType)
    {
        return view('settings.service-types.edit', compact('serviceType'));
    }

    public function update(Request $request, ServiceType $serviceType)
    {
        $data = $this->validateData($request, $serviceType);

        $serviceType->update($data);

        return redirect()
            ->route('settings.service-types.index')
            ->with('success', 'Service type updated.');
    }

    public function toggle(ServiceType $serviceType)
    {
        $serviceType->update(['is_active' => ! (bool) $serviceType->is_active]);

        return redirect()
            ->route('settings.service-types.index')
            ->with('success', 'Service type status updated.');
    }

    public function destroy(ServiceType $serviceType)
    {
        $serviceType->delete();

        return redirect()
            ->route('settings.service-types.index')
            ->with('success', 'Service type deleted.');
    }

    private function validateData(Request $request, ?ServiceType $serviceType = null): array
    {
        $data = $request->validate([
            'key' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('service_types', 'key')->ignore($serviceType?->id),
            ],
            'name' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:10'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
