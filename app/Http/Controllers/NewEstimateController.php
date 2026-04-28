<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NewEstimateController extends BaseServiceController
{
    /**
     * Display estimates using Service Records design
     */
    public function index(Request $request)
    {
        $customerId = $request->input('customer_id');
        $vehicleId = $request->input('vehicle_id');
        
        // Get common data for estimates section
        $data = $this->getCommonData('estimates', $customerId, $vehicleId);
        
        // Add section-specific data
        $data['pageTitle'] = 'Estimates';
        $data['pageDescription'] = 'View and manage all estimates';
        $data['sectionColor'] = 'success';
        $data['sectionIcon'] = 'file-invoice-dollar';
        
        return view('service_sections.estimates', $data);
    }
}