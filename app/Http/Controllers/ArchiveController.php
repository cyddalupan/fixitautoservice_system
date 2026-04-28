<?php

namespace App\Http\Controllers;

use App\Models\Archive;
use App\Models\WorkOrder;
use App\Models\Estimate;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArchiveController extends Controller
{
    /**
     * Display archived records with search/filter/pagination.
     */
    public function index(Request $request)
    {
        $query = Archive::with('archivedBy')->orderBy('archived_at', 'desc');

        // Search
        if ($search = $request->search) {
            $query->search($search);
        }

        // Filter by source module
        if ($module = $request->module) {
            $query->module($module);
        }

        // Date range filter
        if ($request->date_from) {
            $query->where('archived_at', '>=', Carbon::parse($request->date_from)->startOfDay());
        }
        if ($request->date_to) {
            $query->where('archived_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        $archives = $query->paginate(20)->withQueryString();

        // Module counts for stats
        $moduleCounts = Archive::select('source_module', DB::raw('count(*) as total'))
            ->groupBy('source_module')
            ->pluck('total', 'source_module');

        return view('archives.index', compact('archives', 'moduleCounts'));
    }

    /**
     * Show archived record details.
     */
    public function show(Archive $archive)
    {
        $archive->load('archivedBy');
        return view('archives.show', compact('archive'));
    }

    /**
     * Restore a single archived record back to its original module.
     */
    public function restore(Archive $archive)
    {
        $data = $archive->original_data;
        $model = null;

        try {
            DB::beginTransaction();

            switch ($archive->source_module) {
                case 'work_order':
                    // Remove archived-specific fields before restoring
                    unset($data['deleted_at']);
                    unset($data['id']);
                    $data['updated_at'] = now();
                    $model = WorkOrder::withTrashed()->find($archive->archivable_id);
                    if ($model) {
                        $model->restore();
                        $model->update($data);
                    } else {
                        $model = WorkOrder::create($data);
                    }
                    break;

                case 'estimate':
                    unset($data['deleted_at']);
                    unset($data['id']);
                    $model = Estimate::withTrashed()->find($archive->archivable_id);
                    if ($model) {
                        $model->restore();
                        $model->update($data);
                    } else {
                        $model = Estimate::create($data);
                    }
                    break;

                case 'payment':
                    unset($data['deleted_at']);
                    unset($data['id']);
                    $model = Payment::withTrashed()->find($archive->archivable_id);
                    if ($model) {
                        $model->restore();
                        $model->update($data);
                    } else {
                        $model = Payment::create($data);
                    }
                    break;

                case 'invoice':
                    unset($data['deleted_at']);
                    unset($data['id']);
                    $model = Invoice::withTrashed()->find($archive->archivable_id);
                    if ($model) {
                        $model->restore();
                        $model->update($data);
                    } else {
                        $model = Invoice::create($data);
                    }
                    break;

                case 'inspection':
                    unset($data['deleted_at']);
                    unset($data['id']);
                    $model = \App\Models\VehicleInspection::withTrashed()->find($archive->archivable_id);
                    if ($model) {
                        $model->restore();
                        $model->update($data);
                    } else {
                        $model = \App\Models\VehicleInspection::create($data);
                    }
                    break;

                case 'customer':
                    unset($data['deleted_at']);
                    unset($data['id']);
                    $model = \App\Models\Customer::withTrashed()->find($archive->archivable_id);
                    if ($model) {
                        $model->restore();
                        $model->update($data);
                    } else {
                        $model = \App\Models\Customer::create($data);
                    }
                    break;
            }

            // Update archive record
            $archive->update([
                'restored_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('archives.index')
                ->with('success', 'Record restored successfully to ' . ucwords(str_replace('_', ' ', $archive->source_module)) . '.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to restore record: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete a single archived record (Admin only).
     */
    public function destroy(Archive $archive)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'admin'])) {
            return redirect()->back()->with('error', 'Only administrators can permanently delete archive records.');
        }

        $archive->delete();

        return redirect()->route('archives.index')
            ->with('success', 'Archive record permanently deleted.');
    }

    // ─── Batch Operations ─────────────────────────────────────────────

    /**
     * Batch delete by month/year.
     */
    public function batchByMonth(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2099',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $count = Archive::archivedInMonth($request->year, $request->month)->count();

        if ($count === 0) {
            return redirect()->back()->with('info', 'No records found for ' . date('F Y', mktime(0, 0, 0, $request->month, 1, $request->year)) . '.');
        }

        Archive::archivedInMonth($request->year, $request->month)->delete();

        return redirect()->route('archives.index')
            ->with('success', "Deleted {$count} archived records from " . date('F Y', mktime(0, 0, 0, $request->month, 1, $request->year)) . ".");
    }

    /**
     * Batch delete by year range.
     */
    public function batchByYearRange(Request $request)
    {
        $request->validate([
            'year_from' => 'required|integer|min:2000|max:2099',
            'year_to' => 'required|integer|min:2000|max:2099|gte:year_from',
        ]);

        $count = Archive::whereYear('archived_at', '>=', $request->year_from)
            ->whereYear('archived_at', '<=', $request->year_to)
            ->count();

        if ($count === 0) {
            return redirect()->back()->with('info', 'No records found for the selected year range.');
        }

        Archive::whereYear('archived_at', '>=', $request->year_from)
            ->whereYear('archived_at', '<=', $request->year_to)
            ->delete();

        return redirect()->route('archives.index')
            ->with('success', "Deleted {$count} archived records from {$request->year_from} to {$request->year_to}.");
    }

    /**
     * Batch delete by custom date range.
     */
    public function batchByDateRange(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $from = Carbon::parse($request->date_from)->startOfDay();
        $to = Carbon::parse($request->date_to)->endOfDay();

        $count = Archive::archivedBetween($from, $to)->count();

        if ($count === 0) {
            return redirect()->back()->with('info', 'No records found for the selected date range.');
        }

        Archive::archivedBetween($from, $to)->delete();

        return redirect()->route('archives.index')
            ->with('success', "Deleted {$count} archived records from {$from->format('M d, Y')} to {$to->format('M d, Y')}.");
    }

    /**
     * Batch delete selected records (multi-select).
     */
    public function batchDelete(Request $request)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'admin'])) {
            return redirect()->back()->with('error', 'Only administrators can permanently delete archive records.');
        }

        $request->validate([
            'archive_ids' => 'required|array',
            'archive_ids.*' => 'exists:archives,id',
        ]);

        $count = count($request->archive_ids);
        Archive::whereIn('id', $request->archive_ids)->delete();

        return redirect()->route('archives.index')
            ->with('success', "Deleted {$count} archived records.");
    }

    /**
     * Batch restore selected records.
     */
    public function batchRestore(Request $request)
    {
        $request->validate([
            'archive_ids' => 'required|array',
            'archive_ids.*' => 'exists:archives,id',
        ]);

        $archives = Archive::whereIn('id', $request->archive_ids)->get();
        $success = 0;
        $errors = [];

        foreach ($archives as $archive) {
            try {
                $data = $archive->original_data;
                
                switch ($archive->source_module) {
                    case 'work_order':
                        unset($data['deleted_at'], $data['id']);
                        $model = WorkOrder::withTrashed()->find($archive->archivable_id);
                        if ($model) { $model->restore(); $model->update($data); }
                        else { WorkOrder::create($data); }
                        break;

                    case 'estimate':
                        unset($data['deleted_at'], $data['id']);
                        $model = Estimate::withTrashed()->find($archive->archivable_id);
                        if ($model) { $model->restore(); $model->update($data); }
                        else { Estimate::create($data); }
                        break;

                    case 'payment':
                        unset($data['deleted_at'], $data['id']);
                        $model = Payment::withTrashed()->find($archive->archivable_id);
                        if ($model) { $model->restore(); $model->update($data); }
                        else { Payment::create($data); }
                        break;

                    case 'invoice':
                        unset($data['deleted_at'], $data['id']);
                        $model = Invoice::withTrashed()->find($archive->archivable_id);
                        if ($model) { $model->restore(); $model->update($data); }
                        else { Invoice::create($data); }
                        break;

                    case 'inspection':
                        unset($data['deleted_at'], $data['id']);
                        $model = \App\Models\VehicleInspection::withTrashed()->find($archive->archivable_id);
                        if ($model) { $model->restore(); $model->update($data); }
                        else { \App\Models\VehicleInspection::create($data); }
                        break;

                    case 'customer':
                        unset($data['deleted_at'], $data['id']);
                        $model = \App\Models\Customer::withTrashed()->find($archive->archivable_id);
                        if ($model) { $model->restore(); $model->update($data); }
                        else { \App\Models\Customer::create($data); }
                        break;
                }

                $archive->update(['restored_at' => now()]);
                $success++;
            } catch (\Exception $e) {
                $errors[] = "Archive #{$archive->id}: " . $e->getMessage();
            }
        }

        $message = "Restored {$success} records.";
        if (count($errors) > 0) {
            $message .= " Errors: " . implode(', ', $errors);
        }

        return redirect()->route('archives.index')
            ->with('success', $message);
    }
}
