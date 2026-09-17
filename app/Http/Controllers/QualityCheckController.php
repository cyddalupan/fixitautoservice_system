<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QualityCheckController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $qualityChecks = \App\Models\QualityCheck::withCount('jobOrderQualityChecks')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Average completion rate — computed in PHP from checklists with items
        $all = \App\Models\QualityCheck::all();
        $rates = $all->map(fn ($c) => $c->calculateCompletionRate())->filter(fn ($v) => $v !== null);
        $averageCompletionRate = $rates->count() > 0 ? round($rates->avg(), 1) : 0;

        $mostUsedTemplate = \App\Models\QualityCheck::withCount('jobOrderQualityChecks')
            ->orderBy('job_order_quality_checks_count', 'desc')
            ->first();

        return view('quality-control.quality-checks.index', compact('qualityChecks', 'averageCompletionRate', 'mostUsedTemplate'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('quality-control.quality-checks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Implementation would go here
        return redirect()->route('quality-checks.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return view('quality-control.quality-checks.show', compact('id'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('quality-control.quality-checks.edit', compact('id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Implementation would go here
        return redirect()->route('quality-checks.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Implementation would go here
        return redirect()->route('quality-checks.index');
    }

    /**
     * Duplicate a quality check.
     */
    public function duplicate($id)
    {
        // Implementation would go here
        return redirect()->route('quality-checks.index');
    }

    /**
     * Show statistics for a quality check.
     */
    public function statistics($id)
    {
        return view('quality-control.quality-checks.statistics', compact('id'));
    }

    /**
     * Export quality checks.
     */
    public function export(Request $request)
    {
        // Implementation would go here
        return response()->json(['message' => 'Export functionality']);
    }

    /**
     * Bulk update quality checks.
     */
    public function bulkUpdate(Request $request)
    {
        // Implementation would go here
        return redirect()->route('quality-checks.index');
    }
}