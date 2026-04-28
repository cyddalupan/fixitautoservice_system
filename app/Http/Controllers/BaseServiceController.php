<?php

namespace App\Http\Controllers;

use App\Services\ServiceRecordService;
use App\Models\Customer;
use App\Models\Vehicle;

class BaseServiceController extends Controller
{
    protected $serviceRecordService;
    
    public function __construct(ServiceRecordService $serviceRecordService)
    {
        $this->serviceRecordService = $serviceRecordService;
    }
    
    /**
     * Get common data for all service views
     */
    protected function getCommonData($section = null, $customerId = null, $vehicleId = null)
    {
        // Get workflows from centralized service
        $workflows = $this->serviceRecordService->getWorkflows($customerId, $vehicleId, $section);
        
        // Get summary counts
        $summaryCounts = $this->serviceRecordService->getSummaryCounts();
        
        // Get customers and vehicles for filters
        $customers = Customer::orderBy('last_name')->get();
        $vehicles = Vehicle::orderBy('make')->get();
        
        // Extract section-specific data
        $sectionData = [];
        if ($section) {
            $sectionData = $this->serviceRecordService->extractSectionData($workflows, $section);
        }
        
        return [
            'workflows' => $workflows,
            'sectionData' => $sectionData,
            'customers' => $customers,
            'vehicles' => $vehicles,
            'customerId' => $customerId,
            'vehicleId' => $vehicleId,
            'scheduledCount' => $summaryCounts['scheduledCount'],
            'repairOrderCount' => $summaryCounts['repairOrderCount'],
            'estimateCount' => $summaryCounts['estimateCount'],
            'jobOrderCount' => $summaryCounts['jobOrderCount'],
            'section' => $section
        ];
    }
}