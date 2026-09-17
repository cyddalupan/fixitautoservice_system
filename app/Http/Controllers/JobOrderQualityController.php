<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class JobOrderQualityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $qualityChecks = \App\Models\JobOrderQualityCheck::with(['jobOrder.customer', 'technician', 'qualityCheck'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $pendingCount = \App\Models\JobOrderQualityCheck::where('status', \App\Models\JobOrderQualityCheck::STATUS_PENDING)->count();
        $approvedCount = \App\Models\JobOrderQualityCheck::where('status', \App\Models\JobOrderQualityCheck::STATUS_APPROVED)->count();
        $rejectedCount = \App\Models\JobOrderQualityCheck::where('status', \App\Models\JobOrderQualityCheck::STATUS_REJECTED)->count();

        // Average score — computed from results JSON (no DB column)
        $scored = \App\Models\JobOrderQualityCheck::whereNotNull('results')->get()
            ->map(fn ($c) => $c->calculateScore())
            ->filter(fn ($v) => $v !== null);
        $averageScore = $scored->count() > 0 ? round($scored->avg(), 1) : 0;

        $technicians = \App\Models\User::where('role', 'technician')->orderBy('name')->get();
        $supervisors = \App\Models\User::whereIn('role', ['supervisor', 'quality_control', 'super_admin'])->orderBy('name')->get();

        return view('quality-control.job-order-quality.index', compact('qualityChecks', 'pendingCount', 'approvedCount', 'rejectedCount', 'averageScore', 'technicians', 'supervisors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('quality-control.job-order-quality.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Implementation would go here
        return redirect()->route('job-order-quality.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return view('quality-control.job-order-quality.show', compact('id'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('quality-control.job-order-quality.edit', compact('id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Implementation would go here
        return redirect()->route('job-order-quality.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Implementation would go here
        return redirect()->route('job-order-quality.index');
    }
}