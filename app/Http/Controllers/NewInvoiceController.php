<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NewInvoiceController extends BaseServiceController
{
    /**
     * Display invoices & payments using Service Records design
     */
    public function index(Request $request)
    {
        $customerId = $request->input('customer_id');
        $vehicleId = $request->input('vehicle_id');
        
        // Get common data for payments section
        $data = $this->getCommonData('payments', $customerId, $vehicleId);
        
        // Add section-specific data
        $data['pageTitle'] = 'Invoices & Payments';
        $data['pageDescription'] = 'View and manage all invoices and payments';
        $data['sectionColor'] = 'purple';
        $data['sectionIcon'] = 'credit-card';
        
        return view('service_sections.invoices', $data);
    }
}