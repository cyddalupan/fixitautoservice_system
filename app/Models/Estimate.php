<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Estimate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'vehicle_id',
        'appointment_id',
        'inspection_id',
        'estimate_number',
        'issue_date',
        'expiry_date',
        'mileage',
        'labor_hours',
        'labor_rate',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'tax_total',
        'discount_type',
        'discount_value',
        'discount_amount',
        'total_amount',
        'deposit_required',
        'balance_remaining',
        'parts_total',
        'labor_total',
        'notes',
        'internal_notes',
        'customer_notes',
        'terms',
        'status',
        'version',
        'service_advisor_id',
        'approved_at',
        'rejected_at',
        'rejection_reason',
        'sent_at',
        'viewed_at',
        'user_id',
        'approved_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'labor_hours' => 'decimal:2',
        'labor_rate' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'deposit_required' => 'decimal:2',
        'balance_remaining' => 'decimal:2',
        'parts_total' => 'decimal:2',
        'labor_total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(EstimateItem::class)->orderBy('sort_order');
    }

    public function itemGroups(): HasMany
    {
        return $this->hasMany(EstimateItemGroup::class)->orderBy('sort_order');
    }

    public function jobOrder(): HasOne
    {
        return $this->hasOne(JobOrder::class);
    }

        public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

public function serviceAdvisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'service_advisor_id');
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(VehicleInspection::class, 'inspection_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'estimate_id');
    }

    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, Invoice::class, 'estimate_id', 'invoice_id');
    }

    // ── Status Helpers ──

    public const STATUSES = [
        'draft'               => 'Supplier Quotation',
        'sent'                => 'Sent',
        'viewed'              => 'Viewed',
        'waiting_approval'    => 'Waiting Approval',
        'waiting_for_parts'   => 'Waiting for Parts',
        'approved'            => 'Approved',
        'rejected'            => 'Rejected',
        'expired'             => 'Expired',
        'converted_to_job_order' => 'Converted to Work Order',
        'converted_to_repair_order' => 'Converted to Repair Order',
    ];

    public const STATUS_BADGES = [
        'draft'               => 'badge-draft',
        'sent'                => 'badge-sent',
        'viewed'              => 'badge-viewed',
        'waiting_approval'    => 'badge-waiting',
        'waiting_for_parts'   => 'badge-waiting',
        'approved'            => 'badge-approved',
        'rejected'            => 'badge-rejected',
        'expired'             => 'badge-expired',
        'converted_to_job_order' => 'badge-converted',
        'converted_to_repair_order' => 'badge-converted',
    ];

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeNotExpired($query)
    {
        return $query->where('expiry_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    public function isExpired(): bool
    {
        return $this->expiry_date < now();
    }

    public function canConvertToJobOrder(): bool
    {
        return $this->status === 'approved' && !$this->isExpired() && !$this->jobOrder;
    }

    public function getFormattedTotalAttribute(): string
    {
        return '₱' . number_format($this->total_amount, 2);
    }

    /**
     * The Repair Order linked to this quotation (with the findings + groups needed
     * to price it). Returns null for quotations not created from a Repair Order.
     */
    private function linkedInspection(): ?VehicleInspection
    {
        if (! $this->inspection_id) {
            return null;
        }

        $inspection = $this->relationLoaded('inspection')
            ? $this->inspection
            : $this->inspection()->first();

        if ($inspection && (! $inspection->relationLoaded('inspectionFindings') || ! $inspection->relationLoaded('findingGroups'))) {
            $inspection->load(['inspectionFindings', 'findingGroups']);
        }

        return $inspection;
    }

    /**
     * Which slice of the linked Repair Order's findings THIS quotation is pricing:
     * 'locked' | 'unlocked' | 'all'.
     *
     * A Repair Order can carry two sets of findings:
     *   - the approved items that came from the ORIGINAL Repair Quotation (frozen
     *     when the RO got promoted — this is the RO's "From Quotation" tab), and
     *   - items discovered DURING the repair (added after the lock).
     *
     * A quotation created before/at the lock prices the approved set. A quotation
     * created afterwards (e.g. the "New Repair Quotation" re-quote button) prices
     * only the during-repair set — otherwise it would wrongly repeat the old
     * quotation's items/amount.
     */
    public function quotationFindingScope(): string
    {
        $inspection = $this->linkedInspection();
        if (! $inspection || $inspection->findings_locked_at === null) {
            return 'all';
        }

        $createdAfterLock = $this->created_at && $this->created_at->gt($inspection->findings_locked_at);

        return $createdAfterLock ? 'unlocked' : 'locked';
    }

    /**
     * The findings this quotation actually quotes (see quotationFindingScope()).
     * Empty when there is no linked Repair Order.
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\InspectionFinding>
     */
    public function quotedFindings(): \Illuminate\Support\Collection
    {
        $inspection = $this->linkedInspection();
        if (! $inspection) {
            return collect();
        }

        $scope = $this->quotationFindingScope();
        if ($scope === 'all') {
            return collect($inspection->inspectionFindings)->values();
        }

        return collect($inspection->inspectionFindings)
            ->filter(function ($f) use ($inspection, $scope) {
                $locked = $inspection->findingIsLocked($f);

                return $scope === 'locked' ? $locked : ! $locked;
            })
            ->values();
    }

    /**
     * Payments that belong to THIS quotation.
     *
     * Payments are recorded against the Repair Order, not a specific quotation. A
     * quotation owns the payments made while it was the active one: those recorded
     * at/after this quotation was created, and before the next quotation for the same
     * Repair Order was created. This stops a during-repair re-quote from showing the
     * old quotation's verified payment as if it were already paid.
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\RepairOrderPayment>
     */
    public function quotationPayments(): \Illuminate\Support\Collection
    {
        $inspection = $this->linkedInspection();
        if (! $inspection || ! $this->created_at || ! $this->inspection_id) {
            return collect();
        }

        $payments = $inspection->relationLoaded('repairOrderPayments')
            ? $inspection->repairOrderPayments
            : $inspection->repairOrderPayments()->get();

        $from = $this->created_at;
        $until = Estimate::where('inspection_id', $this->inspection_id)
            ->where('id', '!=', $this->id)
            ->where('created_at', '>', $this->created_at)
            ->min('created_at');

        return collect($payments)
            ->filter(function ($p) use ($from, $until) {
                if (! $p->created_at || $p->created_at->lt($from)) {
                    return false;
                }

                return $until === null || $p->created_at->lt($until);
            })
            ->sortByDesc('created_at')
            ->values();
    }

    /**
     * Quotation amount — PARTS side.
     *
     * A Repair Quotation is priced from the linked Repair Order's findings, so the
     * amount the list/Amount column shows must READ those findings: parts = qty x
     * unit_price, EXCLUDING items in "Not Pursued" (is_declined). Quotations that are
     * not linked to a Repair Order fall back to their stored parts_total.
     */
    public function getQuotationPartsTotalAttribute(): float
    {
        $inspection = $this->linkedInspection();
        if ($inspection) {
            return (float) $this->quotedFindings()
                ->filter(fn ($f) => ! (bool) $f->is_declined)
                ->sum(fn ($f) => (float) $f->quantity * (float) ($f->unit_price ?? 0));
        }

        return (float) $this->parts_total;
    }

    /**
     * Quotation amount — LABOR side.
     *
     * Labor = the shared labor of each finding group + the per-item labor of any
     * ungrouped finding (estimated_cost). "Not Pursued" items are excluded. Group
     * labor counts only for the groups that actually hold a quoted finding, so a
     * during-repair re-quote never inherits the old quotation's labor. Falls back to
     * labor_total when there is no linked Repair Order.
     */
    public function getQuotationLaborTotalAttribute(): float
    {
        $inspection = $this->linkedInspection();
        if ($inspection) {
            $quoted = $this->quotedFindings();

            $ungroupedLabor = (float) $quoted
                ->filter(fn ($f) => ! (bool) $f->is_declined && $f->group_id === null)
                ->sum(fn ($f) => (float) ($f->estimated_cost ?? 0));

            $quotedGroupIds = $quoted->pluck('group_id')->filter()->unique()->values();
            $groupLabor = (float) collect($inspection->findingGroups)
                ->whereIn('id', $quotedGroupIds)
                ->sum('labor_cost');

            return $ungroupedLabor + $groupLabor;
        }

        return (float) $this->labor_total;
    }

    /**
     * Total quotation amount the Amount column should display.
     */
    public function getQuotationTotalAttribute(): float
    {
        if ($this->linkedInspection()) {
            return $this->quotation_parts_total + $this->quotation_labor_total;
        }

        return (float) $this->total_amount;
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return '₱' . number_format($this->subtotal, 2);
    }

    public function getFormattedTaxAttribute(): string
    {
        return '₱' . number_format($this->tax_amount, 2);
    }

    public function getFormattedDepositAttribute(): string
    {
        return '₱' . number_format($this->deposit_required, 2);
    }

    public function getFormattedBalanceAttribute(): string
    {
        return '₱' . number_format($this->balance_remaining, 2);
    }

    public function getFormattedPartsTotalAttribute(): string
    {
        return '₱' . number_format($this->parts_total, 2);
    }

    public function getFormattedLaborTotalAttribute(): string
    {
        return '₱' . number_format($this->labor_total, 2);
    }

    public function getFormattedQuotationTotalAttribute(): string
    {
        return '₱' . number_format($this->quotation_total, 2);
    }

    public function getFormattedQuotationPartsTotalAttribute(): string
    {
        return '₱' . number_format($this->quotation_parts_total, 2);
    }

    public function getFormattedQuotationLaborTotalAttribute(): string
    {
        return '₱' . number_format($this->quotation_labor_total, 2);
    }

    public function getFormattedDiscountAttribute(): string
    {
        return '₱' . number_format($this->discount_amount, 2);
    }

    public function getDaysUntilExpiryAttribute(): int
    {
        return now()->diffInDays($this->expiry_date, false);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        $map = [
            'draft'               => 'bg-secondary',
            'sent'                => 'bg-info',
            'viewed'              => 'bg-info',
            'waiting_approval'    => 'bg-warning text-dark',
            'waiting_for_parts'   => 'bg-warning text-dark',
            'approved'            => 'bg-success',
            'rejected'            => 'bg-danger',
            'expired'             => 'bg-dark',
            'converted_to_job_order'  => 'bg-primary',
            'converted_to_repair_order' => 'bg-primary',
        ];
        return $map[$this->status] ?? 'bg-secondary';
    }
}
