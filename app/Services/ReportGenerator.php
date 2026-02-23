<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\WorkOrder;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\ServiceRecord;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportGenerator
{
    /**
     * Generate daily activity report.
     */
    public function generateDailyActivityReport(Carbon $date, array $filters = []): array
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();
        
        // Get appointments for the day
        $appointments = Appointment::whereBetween('scheduled_date', [$startOfDay, $endOfDay])
            ->with(['customer', 'vehicle', 'technician'])
            ->get();
        
        // Get work orders for the day
        $workOrders = WorkOrder::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->with(['customer', 'vehicle', 'technician', 'items'])
            ->get();
        
        // Get invoices for the day
        $invoices = Invoice::whereBetween('invoice_date', [$startOfDay, $endOfDay])
            ->with(['customer', 'items', 'payments'])
            ->get();
        
        // Get payments for the day
        $payments = Payment::whereBetween('payment_date', [$startOfDay, $endOfDay])
            ->with(['invoice', 'customer'])
            ->get();
        
        // Get new customers for the day
        $newCustomers = Customer::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->get();
        
        // Calculate totals
        $totalRevenue = $invoices->sum('total_amount');
        $totalPayments = $payments->sum('amount');
        $completedJobs = $workOrders->where('status', 'completed')->count();
        $inProgressJobs = $workOrders->where('status', 'in_progress')->count();
        
        // Apply filters if provided
        if (!empty($filters)) {
            if (isset($filters['technician_id'])) {
                $appointments = $appointments->where('technician_id', $filters['technician_id']);
                $workOrders = $workOrders->where('technician_id', $filters['technician_id']);
            }
            
            if (isset($filters['service_type'])) {
                $workOrders = $workOrders->filter(function($workOrder) use ($filters) {
                    return $workOrder->service_type === $filters['service_type'];
                });
            }
        }
        
        return [
            'date' => $date->format('Y-m-d'),
            'summary' => [
                'appointments' => $appointments->count(),
                'work_orders' => $workOrders->count(),
                'invoices' => $invoices->count(),
                'payments' => $payments->count(),
                'revenue' => $totalRevenue,
                'payments_received' => $totalPayments,
                'new_customers' => $newCustomers->count(),
                'completed_jobs' => $completedJobs,
                'in_progress_jobs' => $inProgressJobs,
            ],
            'details' => [
                'appointments' => $appointments,
                'work_orders' => $workOrders,
                'invoices' => $invoices,
                'payments' => $payments,
                'new_customers' => $newCustomers,
            ],
            'filters_applied' => $filters,
        ];
    }
    
    /**
     * Generate monthly performance report.
     */
    public function generateMonthlyPerformanceReport(Carbon $date, array $filters = []): array
    {
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        $previousMonth = $date->copy()->subMonth();
        
        // Get data for current month
        $currentMonthData = $this->getMonthlyData($startOfMonth, $endOfMonth, $filters);
        
        // Get data for previous month for comparison
        $previousMonthData = $this->getMonthlyData(
            $previousMonth->copy()->startOfMonth(),
            $previousMonth->copy()->endOfMonth(),
            $filters
        );
        
        // Calculate metrics
        $metrics = [
            'current_month' => [
                'month' => $date->format('F Y'),
                'revenue' => $currentMonthData['revenue'],
                'expenses' => $currentMonthData['expenses'],
                'profit' => $currentMonthData['revenue'] - $currentMonthData['expenses'],
                'jobs_completed' => $currentMonthData['jobs_completed'],
                'avg_job_value' => $currentMonthData['jobs_completed'] > 0 
                    ? $currentMonthData['revenue'] / $currentMonthData['jobs_completed'] 
                    : 0,
                'new_customers' => $currentMonthData['new_customers'],
                'repeat_customers' => $currentMonthData['repeat_customers'],
                'customer_satisfaction' => $currentMonthData['avg_satisfaction'],
            ],
            'previous_month' => [
                'month' => $previousMonth->format('F Y'),
                'revenue' => $previousMonthData['revenue'],
                'expenses' => $previousMonthData['expenses'],
                'profit' => $previousMonthData['revenue'] - $previousMonthData['expenses'],
                'jobs_completed' => $previousMonthData['jobs_completed'],
                'avg_job_value' => $previousMonthData['jobs_completed'] > 0 
                    ? $previousMonthData['revenue'] / $previousMonthData['jobs_completed'] 
                    : 0,
                'new_customers' => $previousMonthData['new_customers'],
                'repeat_customers' => $previousMonthData['repeat_customers'],
                'customer_satisfaction' => $previousMonthData['avg_satisfaction'],
            ],
            'comparison' => [
                'revenue_change' => $this->calculatePercentageChange(
                    $previousMonthData['revenue'],
                    $currentMonthData['revenue']
                ),
                'profit_change' => $this->calculatePercentageChange(
                    $previousMonthData['revenue'] - $previousMonthData['expenses'],
                    $currentMonthData['revenue'] - $currentMonthData['expenses']
                ),
                'jobs_change' => $this->calculatePercentageChange(
                    $previousMonthData['jobs_completed'],
                    $currentMonthData['jobs_completed']
                ),
                'customer_growth' => $this->calculatePercentageChange(
                    $previousMonthData['new_customers'],
                    $currentMonthData['new_customers']
                ),
            ],
        ];
        
        return [
            'report_date' => $date->format('Y-m-d'),
            'metrics' => $metrics,
            'details' => $currentMonthData['details'],
            'filters_applied' => $filters,
        ];
    }
    
    /**
     * Get monthly data for performance report.
     */
    private function getMonthlyData(Carbon $start, Carbon $end, array $filters = []): array
    {
        // Get invoices for the month
        $invoices = Invoice::whereBetween('invoice_date', [$start, $end])
            ->with(['customer', 'items'])
            ->get();
        
        // Get work orders for the month
        $workOrders = WorkOrder::whereBetween('created_at', [$start, $end])
            ->with(['customer', 'vehicle'])
            ->get();
        
        // Get new customers for the month
        $newCustomers = Customer::whereBetween('created_at', [$start, $end])
            ->get();
        
        // Get repeat customers (customers with previous service)
        $repeatCustomers = Customer::whereHas('serviceRecords', function($query) use ($start) {
            $query->where('service_date', '<', $start);
        })
        ->whereHas('serviceRecords', function($query) use ($start, $end) {
            $query->whereBetween('service_date', [$start, $end]);
        })
        ->get();
        
        // Calculate expenses (simplified - would need actual expense data)
        $expenses = $invoices->sum(function($invoice) {
            // Simplified expense calculation: 60% of revenue as cost
            return $invoice->total_amount * 0.6;
        });
        
        // Calculate average customer satisfaction (if available)
        $avgSatisfaction = 0;
        if (class_exists('App\Models\CustomerSatisfactionSurvey')) {
            $avgSatisfaction = \App\Models\CustomerSatisfactionSurvey::whereBetween('created_at', [$start, $end])
                ->avg('rating') ?? 0;
        }
        
        // Apply filters
        if (!empty($filters)) {
            if (isset($filters['service_type'])) {
                $workOrders = $workOrders->filter(function($workOrder) use ($filters) {
                    return $workOrder->service_type === $filters['service_type'];
                });
                
                $invoices = $invoices->filter(function($invoice) use ($filters) {
                    return $invoice->items->contains('service_type', $filters['service_type']);
                });
            }
        }
        
        return [
            'revenue' => $invoices->sum('total_amount'),
            'expenses' => $expenses,
            'jobs_completed' => $workOrders->where('status', 'completed')->count(),
            'new_customers' => $newCustomers->count(),
            'repeat_customers' => $repeatCustomers->count(),
            'avg_satisfaction' => $avgSatisfaction,
            'details' => [
                'invoices' => $invoices,
                'work_orders' => $workOrders,
                'new_customers' => $newCustomers,
                'repeat_customers' => $repeatCustomers,
            ],
        ];
    }
    
    /**
     * Generate customer history report.
     */
    public function generateCustomerHistoryReport(int $customerId = null, array $filters = []): array
    {
        $query = Customer::with([
            'vehicles.serviceRecords' => function($query) use ($filters) {
                if (isset($filters['date_from'])) {
                    $query->where('service_date', '>=', $filters['date_from']);
                }
                if (isset($filters['date_to'])) {
                    $query->where('service_date', '<=', $filters['date_to']);
                }
                if (isset($filters['service_type'])) {
                    $query->where('service_type', $filters['service_type']);
                }
                $query->orderBy('service_date', 'desc');
            },
            'vehicles.serviceRecords.technician',
            'appointments',
            'invoices',
            'payments',
        ]);
        
        // Filter by specific customer or all customers
        if ($customerId) {
            $query->where('id', $customerId);
        }
        
        $customers = $query->get();
        
        // Process customer data
        $customerHistory = [];
        $totalSpent = 0;
        $totalServices = 0;
        
        foreach ($customers as $customer) {
            $customerServices = [];
            $customerTotal = 0;
            $customerServiceCount = 0;
            
            foreach ($customer->vehicles as $vehicle) {
                foreach ($vehicle->serviceRecords as $service) {
                    $customerServices[] = [
                        'vehicle' => $vehicle->make . ' ' . $vehicle->model . ' (' . $vehicle->year . ')',
                        'license_plate' => $vehicle->license_plate,
                        'service_date' => $service->service_date,
                        'service_type' => $service->service_type,
                        'description' => $service->description,
                        'cost' => $service->cost,
                        'technician' => $service->technician ? $service->technician->name : 'N/A',
                        'status' => $service->status,
                    ];
                    
                    $customerTotal += $service->cost;
                    $customerServiceCount++;
                }
            }
            
            // Sort services by date (newest first)
            usort($customerServices, function($a, $b) {
                return strtotime($b['service_date']) - strtotime($a['service_date']);
            });
            
            $customerHistory[] = [
                'customer_id' => $customer->id,
                'customer_name' => $customer->first_name . ' ' . $customer->last_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'total_spent' => $customerTotal,
                'service_count' => $customerServiceCount,
                'avg_service_cost' => $customerServiceCount > 0 ? $customerTotal / $customerServiceCount : 0,
                'first_service' => !empty($customerServices) ? end($customerServices)['service_date'] : null,
                'last_service' => !empty($customerServices) ? $customerServices[0]['service_date'] : null,
                'services' => $customerServices,
                'appointment_count' => $customer->appointments->count(),
                'invoice_count' => $customer->invoices->count(),
                'total_paid' => $customer->payments->sum('amount'),
            ];
            
            $totalSpent += $customerTotal;
            $totalServices += $customerServiceCount;
        }
        
        return [
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'summary' => [
                'total_customers' => count($customerHistory),
                'total_spent' => $totalSpent,
                'total_services' => $totalServices,
                'avg_spent_per_customer' => count($customerHistory) > 0 ? $totalSpent / count($customerHistory) : 0,
                'avg_services_per_customer' => count($customerHistory) > 0 ? $totalServices / count($customerHistory) : 0,
            ],
            'customers' => $customerHistory,
            'filters_applied' => $filters,
        ];
    }
    
    /**
     * Calculate percentage change between two values.
     */
    private function calculatePercentageChange(float $oldValue, float $newValue): float
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }
        
        return (($newValue - $oldValue) / $oldValue) * 100;
    }
    
    /**
     * Export report data to various formats.
     */
    public function exportReport(array $reportData, string $format = 'pdf'): array
    {
        $exportData = [
            'format' => $format,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'data' => $reportData,
        ];
        
        // Add format-specific metadata
        switch ($format) {
            case 'csv':
                $exportData['columns'] = $this->getCsvColumns($reportData);
                $exportData['row_count'] = $this->countRows($reportData);
                break;
                
            case 'excel':
                $exportData['sheets'] = $this->getExcelSheets($reportData);
                $exportData['row_count'] = $this->countRows($reportData);
                break;
                
            case 'pdf':
                $exportData['page_count'] = $this->estimatePageCount($reportData);
                $exportData['has_charts'] = isset($reportData['metrics']);
                break;
        }
        
        return $exportData;
    }
    
    /**
     * Get CSV columns for export.
     */
    private function getCsvColumns(array $reportData): array
    {
        // Simplified implementation
        if (isset($reportData['customers'])) {
            return ['Customer Name', 'Vehicle', 'Service Date', 'Service Type', 'Cost', 'Technician', 'Status'];
        } elseif (isset($reportData['metrics'])) {
            return ['Metric', 'Current Month', 'Previous Month', 'Change %'];
        } else {
            return ['Date', 'Appointments', 'Work Orders', 'Invoices', 'Revenue', 'Payments', 'New Customers'];
        }
    }
    
    /**
     * Count rows in report data.
     */
    private function countRows(array $reportData): int
    {
        if (isset($reportData['customers'])) {
            return count($reportData['customers']);
        } elseif (isset($reportData['details'])) {
            // Count all items in details
            $count = 0;
            foreach ($reportData['details'] as $detail) {
                if (is_countable($detail)) {
                    $count += count($detail);
                }
            }
            return $count;
        }
        
        return 0;
    }
    
    /**
     * Get Excel sheets structure.
     */
    private function getExcelSheets(array $reportData): array
    {
        $sheets = [];
        
        if (isset($reportData['customers'])) {
            $sheets[] = ['name' => 'Customer History', 'data_type' => 'customer_services'];
        }
        
        if (isset($reportData['metrics'])) {
            $sheets[] = ['name' => 'Monthly Metrics', 'data_type' => 'metrics'];
            $sheets[] = ['name' => 'Comparison', 'data_type' => 'comparison'];
        }
        
        if (isset($reportData['summary'])) {
            $sheets[] = ['name' => 'Daily Summary', 'data_type' => 'summary'];
        }
        
        return $sheets;
    }
    
    /**
     * Estimate PDF page count.
     */
    private function estimatePageCount(array $reportData): int
    {
        $rowCount = $this->countRows($reportData);
        $pages = ceil($rowCount / 50); // 50 rows per page
        
        return max(1, $pages);
    }
}