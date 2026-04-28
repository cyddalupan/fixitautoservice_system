<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NewAppointmentController extends BaseServiceController
{
    /**
     * Display appointments using Service Records design
     */
    public function index(Request $request)
    {
        $customerId = $request->input('customer_id');
        $vehicleId = $request->input('vehicle_id');
        
        // Get common data for appointments section
        $data = $this->getCommonData('appointments', $customerId, $vehicleId);
        
        // Add section-specific data
        $data['pageTitle'] = 'Appointments';
        $data['pageDescription'] = 'View and manage all appointments';
        $data['sectionColor'] = 'primary';
        $data['sectionIcon'] = 'calendar-check';
        
        return view('service_sections.appointments', $data);
    }
}