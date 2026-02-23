<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProfitAnalysis;
use App\Models\WorkOrder;
use App\Models\Invoice;
use Carbon\Carbon;

class ProfitAnalysisController extends Controller
{
    /**
     * Display profit analysis dashboard
     */
    public function index()
    {
        $today = now()->format('Y-m-d');
        $startOfMonth = now()->startOfMonth()->format('Y-m-d');
        $startOfYear = now()->startOfYear()->format('Y-m-d');
        
        // Get summary statistics
        $todayAnalysis = ProfitAnalysis::whereDate('analysis_date', $today)->finalized()->get();
        $monthAnalysis = ProfitAnalysis::whereDate('analysis_date', '>=', $startOfMonth)->finalized()->get();
        $yearAnalysis = ProfitAnalysis::whereDate('analysis_date', '>=', $startOfYear)->finalized()->get();
        
        // Calculate totals
        $todayTotals = $this->calculateTotals($todayAnalysis);
        $monthTotals = $this->calculateTotals($monthAnalysis);
        $yearTotals = $this->calculateTotals($yearAnalysis);
        
        // Get recent analyses
        $recentAnalyses = ProfitAnalysis::with(['workOrder', 'invoice'])
            ->orderBy('analysis_date', 'desc')
            ->limit(10)
            ->get();
        
        return view('profit-analysis.index', compact(
            'todayTotals',
            'monthTotals',
            'yearTotals',
            'recentAnalyses'
        ));
    }

    /**
     * Display job profitability reports
     */
    public function jobProfitability(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        $analyses = ProfitAnalysis::with(['workOrder', 'invoice'])
            ->dateRange($startDate, $endDate)
            ->finalized()
            ->orderBy('analysis_date', 'desc')
            ->get();
        
        $summary = $this->calculateTotals($analyses);
        
        return view('profit-analysis.job-profitability', compact(
            'analyses',
            'summary',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Generate profit analysis for a work order
     */
    public function generateForWorkOrder(WorkOrder $workOrder)
    {
        $analysis = ProfitAnalysis::generateFromWorkOrder($workOrder);
        $analysis->save();
        
        return redirect()->route('profit-analysis.show', $analysis)
            ->with('success', 'Profit analysis generated successfully.');
    }

    /**
     * Show a specific profit analysis
     */
    public function show(ProfitAnalysis $profitAnalysis)
    {
        $profitAnalysis->load(['workOrder', 'invoice']);
        
        return view('profit-analysis.show', compact('profitAnalysis'));
    }

    /**
     * Finalize a profit analysis
     */
    public function finalize(ProfitAnalysis $profitAnalysis)
    {
        $profitAnalysis->update([
            'is_finalized' => true,
            'finalized_at' => now(),
        ]);
        
        return redirect()->route('profit-analysis.show', $profitAnalysis)
            ->with('success', 'Profit analysis finalized successfully.');
    }

    /**
     * Delete a profit analysis
     */
    public function destroy(ProfitAnalysis $profitAnalysis)
    {
        $profitAnalysis->delete();
        
        return redirect()->route('profit-analysis.index')
            ->with('success', 'Profit analysis deleted successfully.');
    }

    /**
     * Export profit analysis data
     */
    public function export(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        $analyses = ProfitAnalysis::with(['workOrder', 'invoice'])
            ->dateRange($startDate, $endDate)
            ->finalized()
            ->orderBy('analysis_date')
            ->get();
        
        // In a real implementation, this would generate CSV or Excel file
        // For now, return JSON response
        return response()->json([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_records' => $analyses->count(),
            'analyses' => $analyses->map(function($analysis) {
                return [
                    'analysis_date' => $analysis->analysis_date,
                    'work_order_number' => $analysis->workOrder->work_order_number ?? 'N/A',
                    'invoice_number' => $analysis->invoice->invoice_number ?? 'N/A',
                    'total_revenue' => $analysis->total_revenue,
                    'total_cost' => $analysis->total_cost,
                    'gross_profit' => $analysis->gross_profit,
                    'gross_profit_margin' => $analysis->gross_profit_margin,
                    'net_profit' => $analysis->net_profit,
                    'net_profit_margin' => $analysis->net_profit_margin,
                ];
            }),
            'summary' => $this->calculateTotals($analyses),
        ]);
    }

    /**
     * Calculate totals from a collection of profit analyses
     */
    private function calculateTotals($analyses)
    {
        return [
            'total_revenue' => $analyses->sum('total_revenue'),
            'total_cost' => $analyses->sum('total_cost'),
            'gross_profit' => $analyses->sum('gross_profit'),
            'labor_revenue' => $analyses->sum('labor_revenue'),
            'labor_cost' => $analyses->sum('labor_cost'),
            'labor_profit' => $analyses->sum('labor_profit'),
            'parts_revenue' => $analyses->sum('parts_revenue'),
            'parts_cost' => $analyses->sum('parts_cost'),
            'parts_profit' => $analyses->sum('parts_profit'),
            'net_profit' => $analyses->sum('net_profit'),
            'count' => $analyses->count(),
        ];
    }

    /**
     * Get profit margin trends
     */
    public function marginTrends(Request $request)
    {
        $period = $request->get('period', 'monthly'); // daily, weekly, monthly, yearly
        
        $query = ProfitAnalysis::finalized();
        
        switch ($period) {
            case 'daily':
                $query->selectRaw('DATE(analysis_date) as period, 
                    AVG(gross_profit_margin) as avg_gross_margin,
                    AVG(net_profit_margin) as avg_net_margin,
                    COUNT(*) as count')
                    ->groupByRaw('DATE(analysis_date)')
                    ->orderBy('period', 'desc')
                    ->limit(30);
                break;
                
            case 'weekly':
                $query->selectRaw('YEARWEEK(analysis_date) as period, 
                    AVG(gross_profit_margin) as avg_gross_margin,
                    AVG(net_profit_margin) as avg_net_margin,
                    COUNT(*) as count')
                    ->groupByRaw('YEARWEEK(analysis_date)')
                    ->orderBy('period', 'desc')
                    ->limit(12);
                break;
                
            case 'monthly':
                $query->selectRaw('DATE_FORMAT(analysis_date, "%Y-%m") as period, 
                    AVG(gross_profit_margin) as avg_gross_margin,
                    AVG(net_profit_margin) as avg_net_margin,
                    COUNT(*) as count')
                    ->groupByRaw('DATE_FORMAT(analysis_date, "%Y-%m")')
                    ->orderBy('period', 'desc')
                    ->limit(12);
                break;
                
            case 'yearly':
                $query->selectRaw('YEAR(analysis_date) as period, 
                    AVG(gross_profit_margin) as avg_gross_margin,
                    AVG(net_profit_margin) as avg_net_margin,
                    COUNT(*) as count')
                    ->groupByRaw('YEAR(analysis_date)')
                    ->orderBy('period', 'desc')
                    ->limit(5);
                break;
        }
        
        $trends = $query->get();
        
        return response()->json([
            'period' => $period,
            'trends' => $trends,
        ]);
    }
}
