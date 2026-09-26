<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class VehicleInspection extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Text keywords that mark a Repair Order / service as Preventive
     * Maintenance (PMS). Detection is text-based (not just the exact checklist
     * key) so free-typed job descriptions also count:
     * "Change Oil", "PMS Package", "Preventive Maintenance", "Tune Up", ...
     *
     * NOTE: bare "maintenance" is intentionally NOT a keyword — aircon/
     * underchassis jobs often say "maintenance" too. "preventive" covers the
     * PREVENTIVE MAINTENANCE service type.
     */
    public const PMS_KEYWORDS = [
        'pms',
        'preventive',
        'preventative',
        'change oil',
        'oil change',
        'tune up',
        'tune-up',
        'tune_up',
    ];

    /** Columns on this table that carry the availed-service text. */
    public const PMS_TEXT_COLUMNS = [
        'service_type',
        'service_types',
        'job_description_items',
        'recommended_services',
    ];

    /**
     * Build the raw SQL fragment + bindings for "this Repair Order is a PMS
     * job", usable with any table alias (e.g. 'vehicle_inspections' or 'vi').
     *
     * @return array{0: string, 1: array<int, string>}
     */
    public static function pmsConditionSql(string $alias = 'vehicle_inspections'): array
    {
        $parts = [];
        $bindings = [];
        foreach (self::PMS_TEXT_COLUMNS as $col) {
            foreach (self::PMS_KEYWORDS as $kw) {
                $parts[] = "{$alias}.{$col} LIKE ?";
                $bindings[] = '%'.$kw.'%';
            }
        }

        return ['('.implode(' OR ', $parts).')', $bindings];
    }

    /**
     * Constrain a vehicle_inspections query to Preventive Maintenance (PMS)
     * jobs only, using the keyword detection above.
     */
    public function scopePmsJobs($query)
    {
        [$sql, $bindings] = self::pmsConditionSql();

        return $query->whereRaw($sql, $bindings);
    }

    /** True when this Repair Order looks like a Preventive Maintenance (PMS) job. */
    public function getIsPmsJobAttribute(): bool
    {
        $haystack = strtolower(implode(' ', [
            (string) $this->service_type,
            (string) json_encode($this->service_types),
            (string) json_encode($this->job_description_items),
            (string) json_encode($this->recommended_services),
        ]));

        foreach (self::PMS_KEYWORDS as $kw) {
            if (str_contains($haystack, $kw)) {
                return true;
            }
        }

        return false;
    }

    protected $fillable = [
        'reference_number',
        'service_id',
        'job_order_id',
        'appointment_id',
        'source',
        'date_received',
        'service_types',
        'job_description_items',
        'parts_items',
        'discount',
        'customer_id',
        'vehicle_id',
        'technician_id',
        'service_advisor_id',
        'inspection_type',
        'inspection_status',
        'repair_status',
        'repair_tags',
        'workshop_released_at',
        'findings_locked_at',
        'inspection_name',
        'inspection_notes',
        'service_type',
        'technician_notes',
        'customer_concerns',
        'recommended_services',
        'additional_notes',
        'total_items_checked',
        'items_passed',
        'items_failed',
        'items_attention_needed',
        'items_not_applicable',
        'inspection_score',
        'has_safety_concerns',
        'has_urgent_issues',
        'has_critical_issues',
        'safety_notes',
        'urgent_issues_notes',
        'requires_customer_approval',
        'customer_approved',
        'customer_approval_method',
        'customer_approved_at',
        'customer_approval_notes',
        'customer_signature_path',
        'has_upsell_opportunities',
        'upsell_notes',
        'estimated_upsell_value',
        'actual_upsell_value',
        'vehicle_mileage',
        'photos',
        'videos',
        'documents',
        'attachments',
        'inspection_started_at',
        'inspection_completed_at',
        'report_generated_at',
        'report_sent_at',
        'created_by',
        'updated_by',
        'approved_by',
        'categories',
        'viewed_at',
    ];

    protected $casts = [
        'inspection_type' => 'json',
        'service_types' => 'array',
        'job_description_items' => 'array',
        'parts_items' => 'array',
        'discount' => 'decimal:2',
        'repair_tags' => 'array',
        'date_received' => 'date',
        'workshop_released_at' => 'datetime',
        'findings_locked_at' => 'datetime',
        'photos' => 'array',
        'videos' => 'array',
        'documents' => 'array',
        'attachments' => 'array',
        'has_safety_concerns' => 'boolean',
        'has_urgent_issues' => 'boolean',
        'has_critical_issues' => 'boolean',
        'requires_customer_approval' => 'boolean',
        'customer_approved' => 'boolean',
        'has_upsell_opportunities' => 'boolean',
        'estimated_upsell_value' => 'decimal:2',
        'actual_upsell_value' => 'decimal:2',
        'vehicle_mileage' => 'integer',
        'inspection_score' => 'decimal:2',
        'inspection_started_at' => 'datetime',
        'inspection_completed_at' => 'datetime',
        'report_generated_at' => 'datetime',
        'report_sent_at' => 'datetime',
        'categories' => 'array',
        'findings' => 'array',
        'customer_approved_at' => 'datetime',
        'viewed_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'service_id');
    }

    public function jobOrder()
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Payment records (down / full payment + proof) uploaded against this
     * repair order. See RepairOrderPaymentController.
     */
    public function repairOrderPayments(): HasMany
    {
        return $this->hasMany(RepairOrderPayment::class, 'vehicle_inspection_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function serviceAdvisor()
    {
        return $this->belongsTo(User::class, 'service_advisor_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(InspectionItem::class, 'inspection_id');
    }

    public function inspectionFindings()
    {
        return $this->hasMany(InspectionFinding::class, 'inspection_id')->orderBy('sort_order');
    }

    public function estimate(): HasOne
    {
        return $this->hasOne(Estimate::class, 'inspection_id')->latestOfMany();
    }

    public function findingGroups()
    {
        return $this->hasMany(InspectionFindingGroup::class, 'inspection_id')->orderBy('sort_order');
    }

    /**
     * Chronological activity history of this Repair Order — what has happened on
     * it so far. Assembled read-only from the record itself plus its related
     * records (findings, groups, quotation, work order, payments, appointment).
     * No new tables required.
     *
     * @return array<int, array{time: \Carbon\CarbonInterface, title: string, detail: ?string, icon: string, color: string}>
     */
    public function activityHistory(): array
    {
        $this->loadMissing([
            'inspectionFindings', 'findingGroups', 'estimate', 'estimate.approvedBy',
            'jobOrder', 'jobOrder.qualityChecker', 'repairOrderPayments.uploadedBy',
            'repairOrderPayments.verifiedBy', 'appointment', 'technician', 'createdBy',
        ]);

        $events = [];
        $add = function ($time, string $title, $detail = null, string $icon = 'fa-circle', string $color = 'secondary') use (&$events) {
            if (! $time) {
                return;
            }
            $time = $time instanceof \Carbon\CarbonInterface ? $time : \Carbon\Carbon::parse($time);
            $events[] = ['time' => $time, 'title' => $title, 'detail' => $detail, 'icon' => $icon, 'color' => $color];
        };

        $qtyStr = function ($q) {
            return rtrim(rtrim(number_format((float) ($q ?? 1), 2, '.', ''), '0'), '.');
        };

        // --- The repair order itself ---
        $add($this->created_at, 'Repair Order created', $this->createdBy?->name ? 'by '.$this->createdBy->name : null, 'fa-file-circle-plus', 'primary');

        if ($this->appointment && $this->appointment->checked_in_at) {
            $add($this->appointment->checked_in_at, 'Vehicle checked in', $this->appointment->appointment_number ? 'Appointment '.$this->appointment->appointment_number : null, 'fa-right-to-bracket', 'info');
        }

        // --- Findings ---
        foreach ($this->inspectionFindings->sortBy('created_at') as $f) {
            $label = trim((string) ($f->issue_title ?: $f->part_name ?: 'Finding'));
            $detail = $f->category ? $f->category.' · ' : '';
            $detail .= 'Qty '.$qtyStr($f->quantity);
            if ($f->unit_price !== null) {
                $detail .= ' @ ₱'.number_format((float) $f->unit_price, 2);
            }
            if ($f->is_declined) {
                $label .= ' (Not Pursued)';
            }
            $add($f->created_at, 'Finding added: '.$label, $detail, 'fa-screwdriver-wrench', $f->is_declined ? 'secondary' : 'warning');
        }
        foreach ($this->findingGroups->sortBy('created_at') as $g) {
            $add($g->created_at, 'Group created: '.($g->name ?: 'Group'), ((float) $g->labor_cost > 0) ? 'Shared labor ₱'.number_format((float) $g->labor_cost, 2) : null, 'fa-layer-group', 'info');
        }

        // --- Repair Quotation / Estimate ---
        if ($this->estimate) {
            $e = $this->estimate;
            $add($e->created_at, 'Repair Quotation created', $e->estimate_number ? 'Quotation '.$e->estimate_number : null, 'fa-file-invoice-dollar', 'primary');
            $add($e->sent_at, 'Quotation sent to customer', null, 'fa-paper-plane', 'info');
            $add($e->viewed_at, 'Quotation viewed by customer', null, 'fa-eye', 'info');
            $add($e->approved_at, 'Quotation approved', $e->approvedBy?->name ? 'by '.$e->approvedBy->name : null, 'fa-circle-check', 'success');
            $add($e->rejected_at, 'Quotation rejected', $e->rejection_reason ?: null, 'fa-circle-xmark', 'danger');
        }

        // --- Customer approval of the repair ---
        if ($this->customer_approved_at) {
            $method = $this->customer_approval_method ? 'via '.str_replace('_', ' ', (string) $this->customer_approval_method) : null;
            $add($this->customer_approved_at, 'Customer approved the repair', $method, 'fa-user-check', 'success');
        }

        // --- Work progress ---
        $add($this->inspection_started_at, 'Work started', $this->technician?->name ? 'Technician: '.$this->technician->name : null, 'fa-play', 'info');
        $add($this->inspection_completed_at, 'Work completed', null, 'fa-flag-checkered', 'success');
        $add($this->report_generated_at, 'Report generated', null, 'fa-file-lines', 'primary');
        $add($this->report_sent_at, 'Report sent to customer', null, 'fa-envelope', 'info');
        $add($this->workshop_released_at, 'Released from workshop', null, 'fa-truck-pickup', 'success');

        // --- Linked Work Order ---
        if ($this->jobOrder) {
            $jo = $this->jobOrder;
            $add($jo->created_at, 'Work Order created', $jo->job_order_number ? 'Work Order '.$jo->job_order_number : null, 'fa-clipboard-list', 'primary');
            $add($jo->estimate_approved_at, 'Work Order estimate approved', null, 'fa-thumbs-up', 'success');
            $add($jo->work_start_time, 'Work Order started', null, 'fa-play', 'info');
            $add($jo->work_complete_time, 'Work Order completed', null, 'fa-flag-checkered', 'success');
            $add($jo->quality_check_at, 'Quality check passed', $jo->qualityChecker?->name ? 'by '.$jo->qualityChecker->name : null, 'fa-clipboard-check', 'success');
            $add($jo->invoice_sent_time, 'Invoice sent', null, 'fa-file-invoice', 'info');
            $add($jo->customer_pickup_time, 'Vehicle picked up', null, 'fa-car', 'success');
        }

        // --- Payments (down / full) ---
        foreach ($this->repairOrderPayments->sortBy('created_at') as $p) {
            $type = $p->type_label ?: 'Payment';
            $amt = '₱'.number_format((float) $p->amount, 2);
            $by = $p->uploadedBy?->name ? 'by '.$p->uploadedBy->name : null;
            $add($p->created_at, $type.' uploaded — '.$amt, $p->reference_number ? 'Ref: '.$p->reference_number : $by, 'fa-receipt', 'info');
            $add($p->verified_at, $type.' verified — '.$amt, $p->verifiedBy?->name ? 'by '.$p->verifiedBy->name : null, 'fa-circle-check', 'success');
            if ($p->status === 'rejected') {
                $add($p->updated_at, $type.' rejected — '.$amt, $p->notes ?: null, 'fa-circle-xmark', 'danger');
            }
        }

        // Newest first
        usort($events, fn ($a, $b) => $b['time']->getTimestamp() <=> $a['time']->getTimestamp());

        return $events;
    }

    /**
     * Scopes
     */
    public function scopeDraft($query)
    {
        return $query->where('inspection_status', 'draft');
    }

    public function scopeInProgress($query)
    {
        return $query->where('inspection_status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('inspection_status', 'completed');
    }

    public function scopeApproved($query)
    {
        return $query->where('inspection_status', 'approved');
    }

    public function scopeWithSafetyConcerns($query)
    {
        return $query->where('has_safety_concerns', true);
    }

    public function scopeWithUrgentIssues($query)
    {
        return $query->where('has_urgent_issues', true);
    }

    public function scopeWithCriticalIssues($query)
    {
        return $query->where('has_critical_issues', true);
    }

    public function scopeCustomerApproved($query)
    {
        return $query->where('customer_approved', true);
    }

    public function scopePreService($query)
    {
        return $query->where('inspection_type', 'pre_service');
    }

    public function scopePostService($query)
    {
        return $query->where('inspection_type', 'post_service');
    }

    public function scopeSafety($query)
    {
        return $query->where('inspection_type', 'safety');
    }

    public function scopeComprehensive($query)
    {
        return $query->where('inspection_type', 'comprehensive');
    }

    /**
     * Accessors
     */
    /**
     * Repair status (production-style) workflow.
     * Ordered stages a repair order moves through.
     */
    public const REPAIR_STATUSES = [
        'received'          => ['label' => 'Received',          'bg' => '#f1f5f9', 'text' => '#475569', 'border' => '#e2e8f0'],
        'diagnosing'        => ['label' => 'Diagnosing',        'bg' => '#e9edf2', 'text' => '#475569', 'border' => '#dde3ea'],
        'awaiting_approval' => ['label' => 'Awaiting Approval', 'bg' => '#e2e8f0', 'text' => '#334155', 'border' => '#cbd5e1'],
        'awaiting_parts'    => ['label' => 'Awaiting Parts',    'bg' => '#e2e8f0', 'text' => '#334155', 'border' => '#cbd5e1'],
        'in_progress'       => ['label' => 'In Repair',         'bg' => '#334155', 'text' => '#ffffff', 'border' => '#334155'],
        'quality_check'     => ['label' => 'Quality Check',     'bg' => '#475569', 'text' => '#ffffff', 'border' => '#475569'],
        'ready_for_pickup'  => ['label' => 'Ready for Pickup',  'bg' => '#0f172a', 'text' => '#ffffff', 'border' => '#0f172a'],
        'released'          => ['label' => 'Released',          'bg' => '#0f172a', 'text' => '#ffffff', 'border' => '#0f172a'],
        'paid'              => ['label' => 'Paid',              'bg' => '#f1f5f9', 'text' => '#0f172a', 'border' => '#0f172a'],
        'on_hold'           => ['label' => 'On Hold',           'bg' => '#fee2e2', 'text' => '#dc2626', 'border' => '#fecaca'],
        'cancelled'         => ['label' => 'Cancelled',         'bg' => '#f8fafc', 'text' => '#94a3b8', 'border' => '#e2e8f0'],
    ];

    public function getStatusColorAttribute(): string
    {
        return match($this->inspection_status) {
            'draft' => 'secondary',
            'in_progress' => 'primary',
            'completed' => 'info',
            'approved' => 'success',
            'rejected' => 'warning',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * How the Repair Order entered the shop. `walk_in` = created directly on
     * the Repair Order page (no schedule); `scheduled` = linked to an
     * Appointment / Job Order.
     */
    public const SOURCES = [
        'walk_in'   => ['label' => 'Walk-in',       'icon' => 'fa-person-walking'],
        'scheduled' => ['label' => 'Scheduled',     'icon' => 'fa-calendar-check'],
        'quotation' => ['label' => 'From Quotation', 'icon' => 'fa-file-invoice-dollar'],
    ];

    public function getSourceLabelAttribute(): string
    {
        return self::SOURCES[$this->source]['label']
            ?? ($this->source ? ucfirst(str_replace('_', ' ', (string) $this->source)) : 'Walk-in');
    }

    public function getSourceIconAttribute(): string
    {
        return self::SOURCES[$this->source]['icon'] ?? 'fa-person-walking';
    }

    public function getIsWalkInAttribute(): bool
    {
        // Legacy rows (source = null) with no link to a schedule are walk-ins.
        return ($this->source ?: (($this->appointment_id || $this->job_order_id) ? 'scheduled' : 'walk_in')) === 'walk_in';
    }

    /**
     * True when this Repair Order was promoted from a Repair Quotation.
     */
    public function getIsFromQuotationAttribute(): bool
    {
        return $this->source === 'quotation';
    }

    public function getRepairStatusLabelAttribute(): string
    {
        return self::REPAIR_STATUSES[$this->repair_status]['label']
            ?? ucfirst(str_replace('_', ' ', (string) $this->repair_status));
    }

    /**
     * Human-readable Repair Order reference (e.g. RO-20260924-0001).
     * Falls back to an id-based label for any legacy row without one.
     */
    public function getReferenceLabelAttribute(): string
    {
        return $this->reference_number
            ?: 'RO-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * True when this Repair Order's findings/prices are fixed (promoted from a
     * Repair Quotation) and must not be edited.
     */
    public function isFindingsLocked(): bool
    {
        return $this->findings_locked_at !== null;
    }

    /**
     * True when a single finding is locked.
     *
     * Findings that existed when the RO was locked (promoted from an approved
     * Repair Quotation) are frozen; new findings added afterwards stay editable
     * so the shop can record a problem discovered during the repair.
     */
    public function findingIsLocked($finding): bool
    {
        if ($this->findings_locked_at === null || ! $finding) {
            return false;
        }

        // No timestamp on the row → treat it as part of the approved quotation.
        if (! $finding->created_at) {
            return true;
        }

        return $finding->created_at->lessThanOrEqualTo($this->findings_locked_at);
    }

    public function lockFindings(): void
    {
        if ($this->findings_locked_at === null) {
            $this->forceFill(['findings_locked_at' => now()])->save();
        }
    }

    public function unlockFindings(): void
    {
        if ($this->findings_locked_at !== null) {
            $this->forceFill(['findings_locked_at' => null])->save();
        }
    }

    /**
     * Build the next free Repair Order reference for today.
     */
    public static function generateReferenceNumber(): string
    {
        $prefix = 'RO-' . now()->format('Ymd') . '-';
        $last = static::where('reference_number', 'like', $prefix . '%')
            ->orderBy('reference_number', 'desc')
            ->value('reference_number');
        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Findings on this Repair Order that are NOT fully priced (parts and/or labor
     * still missing). Used to block promotion to a Repair Order until the
     * quotation amounts are final. "Not Pursued" (declined) items are ignored.
     *
     * @return array<int,array{id:int,label:string,missing:array<int,string>}>
     */
    public function pricingGaps(): array
    {
        $this->loadMissing(['inspectionFindings.group', 'findingGroups']);

        $gaps = [];
        foreach ($this->inspectionFindings as $f) {
            if ((bool) $f->is_declined) {
                continue;
            }

            $parts = (float) ($f->unit_price ?? 0);
            $labor = $f->group_id
                ? (float) optional($f->group)->labor_cost
                : (float) ($f->estimated_cost ?? 0);

            $missing = [];
            if ($parts <= 0) { $missing[] = 'parts'; }
            if ($labor <= 0) { $missing[] = 'labor'; }

            if ($missing) {
                $gaps[] = [
                    'id' => (int) $f->id,
                    'label' => $f->issue_title ?: ($f->part_name ?: ('Finding #' . $f->id)),
                    'missing' => $missing,
                ];
            }
        }

        return $gaps;
    }

    public function getRepairStatusHexAttribute(): string
    {
        return self::REPAIR_STATUSES[$this->repair_status]['bg'] ?? '#f1f5f9';
    }

    public function getRepairStatusBgAttribute(): string
    {
        return self::REPAIR_STATUSES[$this->repair_status]['bg'] ?? '#f1f5f9';
    }

    public function getRepairStatusTextAttribute(): string
    {
        return self::REPAIR_STATUSES[$this->repair_status]['text'] ?? '#475569';
    }

    public function getRepairStatusBorderAttribute(): string
    {
        return self::REPAIR_STATUSES[$this->repair_status]['border'] ?? '#e2e8f0';
    }

    public function scopeRepairStatus($query, string $status)
    {
        return $query->where('repair_status', $status);
    }

    /**
     * Stop the workshop clock and record how long the car was in.
     */
    public function markReleased(): void
    {
        if (is_null($this->workshop_released_at)) {
            $this->forceFill(['workshop_released_at' => now()])->save();
        }
    }

    /**
     * Days the vehicle has been (or was) in the workshop.
     * Counts up live until the repair order is tagged Released (then frozen).
     */
    public function getDaysInWorkshopAttribute(): int
    {
        $start = $this->created_at ? $this->created_at->copy()->startOfDay() : now()->startOfDay();
        $end = ($this->workshop_released_at ?? now())->copy()->startOfDay();

        return max(0, (int) $start->diffInDays($end));
    }

    public function getDaysInWorkshopLabelAttribute(): string
    {
        $d = $this->days_in_workshop;

        if ($d === 0) {
            return 'Today';
        }

        return $d . ' ' . ($d === 1 ? 'day' : 'days');
    }

    public function getIsReleasedAttribute(): bool
    {
        return ! is_null($this->workshop_released_at)
            || in_array($this->repair_status, ['released', 'paid'], true);
    }

    /**
     * Repair order totals, taken from the repair order's own line items
     * (Job Description labor + Parts/Supplies cost − discount) — same math as
     * the printed Repair Order slip.
     */
    protected ?array $repairTotalsMemo = null;

    protected function repairTotals(): array
    {
        if ($this->repairTotalsMemo !== null) {
            return $this->repairTotalsMemo;
        }

        $appt = $this->relationLoaded('appointment') ? $this->appointment : $this->appointment()->first();

        // The Repair Order's own intake items are the source of truth; fall back
        // to the linked Appointment for older / appointment-driven orders.
        $jd = $this->job_description_items ?: ($appt->job_description_items ?? []);
        $parts = $this->parts_items ?: ($appt->parts_items ?? []);
        $jd = is_array($jd) ? $jd : [];
        $parts = is_array($parts) ? $parts : [];

        $labor = 0.0;
        foreach ($jd as $row) { $labor += (float) ($row['labor_cost'] ?? 0); }
        $partsTotal = 0.0;
        foreach ($parts as $row) { $partsTotal += (float) ($row['cost'] ?? 0); }
        $discount = (float) ($this->discount ?: ($appt->discount ?? 0));

        return $this->repairTotalsMemo = [
            'labor' => $labor,
            'parts' => $partsTotal,
            'discount' => $discount,
            'total' => max(0, $labor + $partsTotal - $discount),
        ];
    }

    public function getRepairLaborTotalAttribute(): float
    {
        return $this->repairTotals()['labor'];
    }

    public function getRepairPartsTotalAttribute(): float
    {
        return $this->repairTotals()['parts'];
    }

    public function getRepairTotalAttribute(): float
    {
        return $this->repairTotals()['total'];
    }

    public function getRepairTotalFormattedAttribute(): string
    {
        return '₱' . number_format($this->repair_total, 2);
    }

    public function getRepairLaborTotalFormattedAttribute(): string
    {
        return '₱' . number_format($this->repair_labor_total, 2);
    }

    public function getRepairPartsTotalFormattedAttribute(): string
    {
        return '₱' . number_format($this->repair_parts_total, 2);
    }

    /**
     * Build a Repair Order's intake lines (Job Description labor + Parts/Supplies)
     * from a set of findings and their shared labor groups. Shared labor is keyed by
     * the group name; an ungrouped finding's own labor (estimated_cost) folds into
     * its category. Declined ("Not Pursued") findings are skipped.
     *
     * Used when a Repair Quotation is promoted into a Repair Order (priced only
     * from that quotation's own findings) and when rebuilding an existing RO's lines.
     *
     * @param  iterable<int, \App\Models\InspectionFinding>  $findings
     * @param  iterable<int, \App\Models\InspectionFindingGroup>  $groups
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, array<string, mixed>>}
     */
    public static function buildRepairLinesFromFindings($findings, $groups): array
    {
        $jd = [];
        $parts = [];

        $findings = collect($findings);
        $groups = collect($groups);
        $groupIds = $findings->pluck('group_id')->filter()->unique()->values();

        $laborByCat = [];
        $catOrder = [];
        $addLabor = function (string $name, float $amount) use (&$laborByCat, &$catOrder) {
            $name = trim($name) !== '' ? trim($name) : 'Labor';
            $key = mb_strtolower($name);
            if (! isset($laborByCat[$key])) {
                $laborByCat[$key] = ['description' => $name, 'mh' => null, 'unit_price' => null, 'labor_cost' => 0.0];
                $catOrder[] = $key;
            }
            $laborByCat[$key]['labor_cost'] += $amount;
        };

        // Shared labor — only from the groups that actually hold a quoted finding.
        foreach ($groups as $g) {
            if (! $groupIds->contains($g->id)) {
                continue;
            }
            $addLabor((string) ($g->name ?: 'Labor'), (float) $g->labor_cost);
        }

        foreach ($findings as $f) {
            if ((bool) $f->is_declined) {
                continue;
            }

            $qty = (float) ($f->quantity ?: 0);
            $unit = (float) ($f->unit_price ?? 0);

            // Per-item labor of an *ungrouped* finding — attributed to its
            // category (folds into the matching group's labor when one exists).
            if (! $f->group_id && (float) $f->estimated_cost > 0) {
                $addLabor((string) ($f->category ?: 'Labor'), (float) $f->estimated_cost);
            }

            if ($qty > 0 && $unit > 0) {
                $parts[] = [
                    'description' => $f->part_name ?: ($f->issue_title ?: 'Part'),
                    'qty' => $qty,
                    'unit_price' => $unit,
                    'cost' => $qty * $unit,
                ];
            }
        }

        foreach ($catOrder as $key) {
            $jd[] = $laborByCat[$key];
        }

        return [$jd, $parts];
    }

    public function getTypeLabelAttribute(): string
    {
        // Handle array of inspection types
        if (is_array($this->inspection_type)) {
            $labels = [];
            foreach ($this->inspection_type as $type) {
                $labels[] = match($type) {
                    'pre_service' => 'Pre-Service',
                    'post_service' => 'Post-Service',
                    'pre_purchase' => 'Pre-Purchase',
                    'safety' => 'Safety',
                    'emissions' => 'Emissions',
                    'routine' => 'Routine',
                    'diagnostic' => 'Diagnostic',
                    'post_repair' => 'Post-Repair',
                    'comprehensive' => 'Comprehensive',
                    'custom' => 'Custom',
                    default => ucfirst(str_replace('_', ' ', $type)),
                };
            }
            
            if (count($labels) === 1) {
                return $labels[0] . ' Inspection';
            } else {
                return 'Multiple: ' . implode(', ', $labels);
            }
        }
        
        // Handle single string (backward compatibility)
        return match($this->inspection_type) {
            'pre_service' => 'Pre-Service Inspection',
            'post_service' => 'Post-Service Inspection',
            'safety' => 'Safety Inspection',
            'comprehensive' => 'Comprehensive Inspection',
            'custom' => 'Custom Inspection',
            default => ucfirst(str_replace('_', ' ', $this->inspection_type)),
        };
    }

    public function getTypeColorAttribute(): string
    {
        // Handle array of inspection types
        if (is_array($this->inspection_type)) {
            // Return primary color for multiple types
            return 'primary';
        }
        
        // Handle single string (backward compatibility)
        return match($this->inspection_type) {
            'pre_service' => 'primary',
            'post_service' => 'success',
            'safety' => 'warning',
            'comprehensive' => 'info',
            'custom' => 'secondary',
            default => 'secondary',
        };
    }

    public function getFormattedScoreAttribute(): string
    {
        return $this->inspection_score ? number_format($this->inspection_score, 1) . '%' : 'N/A';
    }

    public function getPassRateAttribute(): float
    {
        if ($this->total_items_checked > 0) {
            return ($this->items_passed / $this->total_items_checked) * 100;
        }
        return 0;
    }

    public function getFormattedPassRateAttribute(): string
    {
        return number_format($this->pass_rate, 1) . '%';
    }

    public function getHasMediaAttribute(): bool
    {
        return !empty($this->photos) || !empty($this->videos) || !empty($this->documents);
    }

    public function getMediaCountAttribute(): int
    {
        $count = 0;
        if ($this->photos) $count += count($this->photos);
        if ($this->videos) $count += count($this->videos);
        if ($this->documents) $count += count($this->documents);
        return $count;
    }

    public function getEstimatedUpsellFormattedAttribute(): string
    {
        return $this->estimated_upsell_value ? '$' . number_format($this->estimated_upsell_value, 2) : 'N/A';
    }

    public function getActualUpsellFormattedAttribute(): string
    {
        return $this->actual_upsell_value ? '$' . number_format($this->actual_upsell_value, 2) : 'N/A';
    }

    public function getInspectionDurationAttribute(): ?int
    {
        if ($this->inspection_started_at && $this->inspection_completed_at) {
            return $this->inspection_started_at->diffInMinutes($this->inspection_completed_at);
        }
        return null;
    }

    public function getFormattedDurationAttribute(): string
    {
        if ($duration = $this->inspection_duration) {
            if ($duration < 60) {
                return $duration . ' minutes';
            }
            return floor($duration / 60) . 'h ' . ($duration % 60) . 'm';
        }
        return 'N/A';
    }

    /**
     * Methods
     */
    public function startInspection(): bool
    {
        if ($this->inspection_status === 'draft') {
            $this->update([
                'inspection_status' => 'in_progress',
                'inspection_started_at' => now(),
            ]);
            return true;
        }
        return false;
    }

    public function completeInspection(): bool
    {
        // Allow completion from both 'draft' and 'in_progress' statuses
        if ($this->inspection_status === 'draft' || $this->inspection_status === 'in_progress') {
            $this->update([
                'inspection_status' => 'completed',
                'inspection_completed_at' => now(),
            ]);
            return true;
        }
        return false;
    }

    public function undoCompleteInspection(): bool
    {
        if ($this->inspection_status === 'completed') {
            $this->update([
                'inspection_status' => 'in_progress',
                'inspection_completed_at' => null,
            ]);
            return true;
        }
        return false;
    }

    public function approveInspection(User $approver): bool
    {
        if ($this->inspection_status === 'completed') {
            $this->update([
                'inspection_status' => 'approved',
                'approved_by' => $approver->id,
            ]);
            return true;
        }
        return false;
    }

    public function requestCustomerApproval(string $method = 'digital_signature'): bool
    {
        if ($this->inspection_status === 'completed') {
            $this->update([
                'requires_customer_approval' => true,
                'customer_approval_method' => $method,
            ]);
            return true;
        }
        return false;
    }

    public function approveByCustomer(string $method = 'digital_signature', string $notes = null): bool
    {
        $this->update([
            'customer_approved' => true,
            'customer_approved_at' => now(),
            'customer_approval_method' => $method,
            'customer_approval_notes' => $notes,
        ]);
        return true;
    }

    public function addPhoto(string $path, string $description = null): void
    {
        $photos = $this->photos ?? [];
        $photos[] = [
            'path' => $path,
            'description' => $description,
            'uploaded_at' => now()->toISOString(),
        ];
        $this->update(['photos' => $photos]);
    }

    public function addVideo(string $path, string $description = null): void
    {
        $videos = $this->videos ?? [];
        $videos[] = [
            'path' => $path,
            'description' => $description,
            'uploaded_at' => now()->toISOString(),
        ];
        $this->update(['videos' => $videos]);
    }

    public function addDocument(string $path, string $name, string $type = 'pdf'): void
    {
        $documents = $this->documents ?? [];
        $documents[] = [
            'path' => $path,
            'name' => $name,
            'type' => $type,
            'uploaded_at' => now()->toISOString(),
        ];
        $this->update(['documents' => $documents]);
    }

    public function calculateScore(): void
    {
        $total = $this->total_items_checked;
        $passed = $this->items_passed;
        
        if ($total > 0) {
            $score = ($passed / $total) * 100;
            $this->update(['inspection_score' => $score]);
        }
    }

    public function updateItemCounts(): void
    {
        $items = $this->items;
        
        $counts = [
            'total' => $items->count(),
            'passed' => $items->where('item_status', 'passed')->count(),
            'failed' => $items->where('item_status', 'failed')->count(),
            'attention_needed' => $items->where('item_status', 'attention_needed')->count(),
            'not_applicable' => $items->where('item_status', 'not_applicable')->count(),
        ];
        
        $this->update([
            'total_items_checked' => $counts['total'],
            'items_passed' => $counts['passed'],
            'items_failed' => $counts['failed'],
            'items_attention_needed' => $counts['attention_needed'],
            'items_not_applicable' => $counts['not_applicable'],
        ]);
        
        // Update safety/urgent flags
        $hasSafety = $items->where('is_safety_issue', true)->isNotEmpty();
        $hasUrgent = $items->where('is_urgent_issue', true)->isNotEmpty();
        $hasCritical = $items->where('is_critical_issue', true)->isNotEmpty();
        
        $this->update([
            'has_safety_concerns' => $hasSafety,
            'has_urgent_issues' => $hasUrgent,
            'has_critical_issues' => $hasCritical,
        ]);
        
        // Calculate upsell opportunities
        $estimatedUpsell = $items->where('requires_attention', true)
            ->where('estimated_cost', '>', 0)
            ->sum('estimated_cost');
        
        $this->update([
            'has_upsell_opportunities' => $estimatedUpsell > 0,
            'estimated_upsell_value' => $estimatedUpsell,
        ]);
        
        // Recalculate score
        $this->calculateScore();
    }

    public function generateReport(): array
    {
        return [
            'inspection_id' => $this->id,
            'inspection_number' => 'INSP-' . str_pad($this->id, 6, '0', STR_PAD_LEFT),
            'customer_name' => $this->customer->full_name,
            'vehicle_info' => $this->vehicle->vehicle_info,
            'inspection_type' => $this->type_label,
            'inspection_date' => $this->created_at->format('F d, Y'),
            'technician_name' => $this->technician ? $this->technician->name : 'Not assigned',
            'inspection_score' => $this->formatted_score,
            'pass_rate' => $this->formatted_pass_rate,
            'total_items' => $this->total_items_checked,
            'items_passed' => $this->items_passed,
            'items_failed' => $this->items_failed,
            'items_attention_needed' => $this->items_attention_needed,
            'safety_concerns' => $this->has_safety_concerns,
            'urgent_issues' => $this->has_urgent_issues,
            'critical_issues' => $this->has_critical_issues,
            'customer_approved' => $this->customer_approved,
            'estimated_upsell' => $this->estimated_upsell_formatted,
            'inspection_duration' => $this->formatted_duration,
            'media_count' => $this->media_count,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    public static function createFromTemplate(int $jobOrderId, int $customerId, int $vehicleId, string $templateType = 'pre_service'): self
    {
        $inspection = self::create([
            'job_order_id' => $jobOrderId,
            'customer_id' => $customerId,
            'vehicle_id' => $vehicleId,
            'inspection_type' => $templateType,
            'inspection_status' => 'draft',
            'inspection_name' => ucfirst($templateType) . ' Inspection',
            'requires_customer_approval' => true,
        ]);

        // In production, you would load items from a template database
        // For now, we'll create a basic inspection structure
        $inspection->createDefaultItems();

        return $inspection;
    }

    public function createDefaultItems(): void
    {
        $defaultItems = [
            [
                'item_name' => 'Engine Oil Level',
                'item_type' => 'check',
                'item_description' => 'Check engine oil level and condition',
                'item_unit' => null,
                'spec_source' => 'Manufacturer',
            ],
            [
                'item_name' => 'Brake Fluid Level',
                'item_type' => 'check',
                'item_description' => 'Check brake fluid level and condition',
                'item_unit' => null,
                'spec_source' => 'Manufacturer',
            ],
            [
                'item_name' => 'Coolant Level',
                'item_type' => 'check',
                'item_description' => 'Check coolant level and condition',
                'item_unit' => null,
                'spec_source' => 'Manufacturer',
            ],
            [
                'item_name' => 'Brake Pad Thickness',
                'item_type' => 'measurement',
                'item_description' => 'Measure front and rear brake pad thickness',
                'item_unit' => 'mm',
                'min_value' => 3.0,
                'max_value' => null,
                'spec_source' => 'Safety Standard',
            ],
            [
                'item_name' => 'Tire Tread Depth',
                'item_type' => 'measurement',
                'item_description' => 'Measure tire tread depth on all four tires',
                'item_unit' => 'mm',
                'min_value' => 1.6,
                'max_value' => null,
                'spec_source' => 'Safety Standard',
            ],
            [
                'item_name' => 'Battery Voltage',
                'item_type' => 'measurement',
                'item_description' => 'Test battery voltage and condition',
                'item_unit' => 'volts',
                'min_value' => 12.4,
                'max_value' => 12.8,
                'spec_source' => 'Manufacturer',
            ],
            [
                'item_name' => 'Headlights Operation',
                'item_type' => 'check',
                'item_description' => 'Check all headlights, high beams, and indicators',
                'item_unit' => null,
                'spec_source' => 'Safety Standard',
            ],
            [
                'item_name' => 'Brake Lights Operation',
                'item_type' => 'check',
                'item_description' => 'Check brake lights and third brake light',
                'item_unit' => null,
                'spec_source' => 'Safety Standard',
            ],
            [
                'item_name' => 'Windshield Wipers',
                'item_type' => 'check',
                'item_description' => 'Check wiper blades and washer fluid',
                'item_unit' => null,
                'spec_source' => 'Safety Standard',
            ],
            [
                'item_name' => 'Air Filter Condition',
                'item_type' => 'check',
                'item_description' => 'Inspect engine air filter',
                'item_unit' => null,
                'spec_source' => 'Maintenance',
            ],
        ];

        foreach ($defaultItems as $index => $itemData) {
            InspectionItem::create([
                'inspection_id' => $this->id,
                'item_name' => $itemData['item_name'],
                'item_type' => $itemData['item_type'],
                'item_description' => $itemData['item_description'],
                'item_unit' => $itemData['item_unit'] ?? null,
                'min_value' => $itemData['min_value'] ?? null,
                'max_value' => $itemData['max_value'] ?? null,
                'spec_source' => $itemData['spec_source'],
                'item_status' => 'pending',
                'sequence' => $index + 1,
            ]);
        }

        $this->updateItemCounts();
    }

    /**
     * Get the service progress record for this inspection.
     */
    public function serviceProgress(): HasOne
    {
        return $this->hasOne(ServiceProgress::class, 'inspection_id');
    }

    /**
     * The technicians assigned to this inspection.
     */
    public function technicians(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'inspection_technician', 'inspection_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }
}