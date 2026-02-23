<?php

namespace App\Http\Controllers;

use App\Models\QualityControlSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QualityControlSettingsController extends Controller
{
    /**
     * Display a listing of quality control settings.
     */
    public function index()
    {
        // Initialize defaults if needed
        QualityControlSetting::initializeDefaults();
        
        $settings = QualityControlSetting::orderBy('setting_key')->get();
        $dataTypes = QualityControlSetting::getDataTypes();
        $defaultSettings = QualityControlSetting::getDefaultSettings();

        return view('quality-control.settings.index', compact('settings', 'dataTypes', 'defaultSettings'));
    }

    /**
     * Show the form for creating a new quality control setting.
     */
    public function create()
    {
        $dataTypes = QualityControlSetting::getDataTypes();
        $defaultSettings = QualityControlSetting::getDefaultSettings();

        return view('quality-control.settings.create', compact('dataTypes', 'defaultSettings'));
    }

    /**
     * Store a newly created quality control setting.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'setting_key' => 'required|string|max:100|unique:quality_control_settings,setting_key',
            'setting_value' => 'required|string',
            'data_type' => 'required|string|in:string,integer,boolean,array,json',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Validate value based on data type
        $validationError = $this->validateValueByDataType($data['data_type'], $data['setting_value']);
        if ($validationError) {
            return redirect()->back()
                ->with('error', $validationError)
                ->withInput();
        }

        QualityControlSetting::create($data);

        return redirect()->route('quality-control.settings.index')
            ->with('success', 'Quality control setting created successfully.');
    }

    /**
     * Show the form for editing the specified quality control setting.
     */
    public function edit($id)
    {
        $setting = QualityControlSetting::findOrFail($id);
        $dataTypes = QualityControlSetting::getDataTypes();

        return view('quality-control.settings.edit', compact('setting', 'dataTypes'));
    }

    /**
     * Update the specified quality control setting.
     */
    public function update(Request $request, $id)
    {
        $setting = QualityControlSetting::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'setting_value' => 'required|string',
            'data_type' => 'required|string|in:string,integer,boolean,array,json',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Validate value based on data type
        $validationError = $this->validateValueByDataType($data['data_type'], $data['setting_value']);
        if ($validationError) {
            return redirect()->back()
                ->with('error', $validationError)
                ->withInput();
        }

        $setting->update($data);

        return redirect()->route('quality-control.settings.index')
            ->with('success', 'Quality control setting updated successfully.');
    }

    /**
     * Remove the specified quality control setting.
     */
    public function destroy($id)
    {
        $setting = QualityControlSetting::findOrFail($id);
        
        // Don't allow deletion of default settings
        $defaultSettings = QualityControlSetting::getDefaultSettings();
        if (array_key_exists($setting->setting_key, $defaultSettings)) {
            return redirect()->route('quality-control.settings.index')
                ->with('error', 'Cannot delete default settings. You can only modify their values.');
        }

        $setting->delete();

        return redirect()->route('quality-control.settings.index')
            ->with('success', 'Quality control setting deleted successfully.');
    }

    /**
     * Validate value based on data type.
     */
    private function validateValueByDataType(string $dataType, string $value): ?string
    {
        switch ($dataType) {
            case QualityControlSetting::DATA_TYPE_INTEGER:
                if (!is_numeric($value)) {
                    return 'Value must be a valid integer for integer data type.';
                }
                break;
                
            case QualityControlSetting::DATA_TYPE_BOOLEAN:
                $validBooleans = ['true', 'false', '1', '0', 'yes', 'no'];
                if (!in_array(strtolower($value), $validBooleans)) {
                    return 'Value must be a valid boolean (true/false, 1/0, yes/no).';
                }
                break;
                
            case QualityControlSetting::DATA_TYPE_JSON:
                json_decode($value);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return 'Value must be valid JSON.';
                }
                break;
                
            case QualityControlSetting::DATA_TYPE_ARRAY:
                // For array type, we accept comma-separated values
                // No specific validation needed
                break;
                
            case QualityControlSetting::DATA_TYPE_STRING:
                // Always valid
                break;
        }

        return null;
    }

    /**
     * Reset a setting to its default value.
     */
    public function resetToDefault($id)
    {
        $setting = QualityControlSetting::findOrFail($id);
        $defaultSettings = QualityControlSetting::getDefaultSettings();

        if (array_key_exists($setting->setting_key, $defaultSettings)) {
            $default = $defaultSettings[$setting->setting_key];
            $setting->setting_value = $default['value'];
            $setting->data_type = $default['data_type'];
            $setting->description = $default['description'];
            $setting->save();

            return redirect()->route('quality-control.settings.index')
                ->with('success', 'Setting reset to default value.');
        }

        return redirect()->route('quality-control.settings.index')
            ->with('error', 'No default value found for this setting.');
    }

    /**
     * Reset all settings to defaults.
     */
    public function resetAll()
    {
        // Delete all custom settings
        $defaultSettings = QualityControlSetting::getDefaultSettings();
        $defaultKeys = array_keys($defaultSettings);
        
        // Delete settings that are not in defaults
        QualityControlSetting::whereNotIn('setting_key', $defaultKeys)->delete();
        
        // Reset default settings to their default values
        foreach ($defaultSettings as $key => $config) {
            QualityControlSetting::updateOrCreate(
                ['setting_key' => $key],
                [
                    'setting_value' => $config['value'],
                    'data_type' => $config['data_type'],
                    'description' => $config['description'],
                ]
            );
        }

        return redirect()->route('quality-control.settings.index')
            ->with('success', 'All settings reset to defaults.');
    }

    /**
     * Bulk update settings.
     */
    public function bulkUpdate(Request $request)
    {
        $settings = $request->get('settings', []);
        
        foreach ($settings as $key => $value) {
            if (QualityControlSetting::has($key)) {
                QualityControlSetting::set($key, $value);
            }
        }

        return redirect()->route('quality-control.settings.index')
            ->with('success', 'Settings updated successfully.');
    }

    /**
     * Export settings to CSV.
     */
    public function export()
    {
        $settings = QualityControlSetting::orderBy('setting_key')->get();
        
        $csvData = [];
        $csvData[] = ['Quality Control Settings Export', 'Generated: ' . now()->format('Y-m-d H:i:s')];
        $csvData[] = [];
        $csvData[] = ['Setting Key', 'Value', 'Data Type', 'Description'];
        
        foreach ($settings as $setting) {
            $csvData[] = [
                $setting->setting_key,
                $setting->setting_value,
                $setting->data_type,
                $setting->description,
            ];
        }
        
        $filename = 'quality_control_settings_' . date('Y-m-d_H-i-s') . '.csv';
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
     * Import settings from CSV.
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:csv,txt',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $file = $request->file('import_file');
        $path = $file->getRealPath();
        
        $imported = 0;
        $skipped = 0;
        
        if (($handle = fopen($path, 'r')) !== false) {
            $header = fgetcsv($handle); // Skip header
            
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) >= 3) {
                    $key = $row[0] ?? null;
                    $value = $row[1] ?? null;
                    $dataType = $row[2] ?? 'string';
                    $description = $row[3] ?? null;
                    
                    if ($key && $value) {
                        // Validate data type
                        $dataTypes = QualityControlSetting::getDataTypes();
                        if (!array_key_exists($dataType, $dataTypes)) {
                            $skipped++;
                            continue;
                        }
                        
                        // Create or update setting
                        QualityControlSetting::updateOrCreate(
                            ['setting_key' => $key],
                            [
                                'setting_value' => $value,
                                'data_type' => $dataType,
                                'description' => $description,
                            ]
                        );
                        $imported++;
                    } else {
                        $skipped++;
                    }
                }
            }
            fclose($handle);
        }

        return redirect()->route('quality-control.settings.index')
            ->with('success', "Settings imported successfully. Imported: {$imported}, Skipped: {$skipped}");
    }

    /**
     * Get setting value via API.
     */
    public function getSetting($key)
    {
        $value = QualityControlSetting::get($key);
        
        if ($value === null) {
            return response()->json([
                'error' => 'Setting not found',
            ], 404);
        }

        return response()->json([
            'key' => $key,
            'value' => $value,
        ]);
    }

    /**
     * Set setting value via API.
     */
    public function setSetting(Request $request, $key)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid request',
                'errors' => $validator->errors(),
            ], 400);
        }

        $success = QualityControlSetting::set($key, $request->value);
        
        if ($success) {
            return response()->json([
                'key' => $key,
                'value' => QualityControlSetting::get($key),
                'message' => 'Setting updated successfully',
            ]);
        }

        return response()->json([
            'error' => 'Failed to update setting',
        ], 500);
    }

    /**
     * Get all settings via API.
     */
    public function getAllSettings()
    {
        $settings = QualityControlSetting::getAll();
        
        return response()->json([
            'settings' => $settings,
            'count' => count($settings),
        ]);
    }
}