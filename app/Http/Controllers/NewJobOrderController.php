<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NewJobOrderController extends BaseServiceController
{
    /**
     * Display job orders using Service Records design
     */
    public function index(Request $request)
    {
        $customerId = $request->input('customer_id');
        $vehicleId = $request->input('vehicle_id');
        
        // Get common data for work_orders section (job orders = work orders)
        $data = $this->getCommonData('work_orders', $customerId, $vehicleId);
        
        // Add section-specific data
        $data['pageTitle'] = 'Job Orders';
        $data['pageDescription'] = 'View and manage all job orders';
        $data['sectionColor'] = 'warning';
        $data['sectionIcon'] = 'clipboard-check';
        
        return view('service_sections.job_orders', $data);
    }
}