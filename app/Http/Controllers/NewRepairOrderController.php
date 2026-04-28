<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NewRepairOrderController extends BaseServiceController
{
    /**
     * Display repair orders (work orders) using Service Records design
     */
    public function index(Request $request)
    {
        $customerId = $request->input('customer_id');
        $vehicleId = $request->input('vehicle_id');
        
        // Get common data for work_orders section
        $data = $this->getCommonData('work_orders', $customerId, $vehicleId);
        
        // Add section-specific data
        $data['pageTitle'] = 'Repair Orders';
        $data['pageDescription'] = 'View and manage all repair orders';
        $data['sectionColor'] = 'danger';
        $data['sectionIcon'] = 'wrench';
        
        return view('service_sections.repair_orders', $data);
    }
}