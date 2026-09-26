<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Customer extends Model
{
    use HasFactory;

    /** Months between Preventive Maintenance Service (PMS) intervals. */
    public const PMS_INTERVAL_MONTHS = 6;

    /** A PMS due within this many days is shown as "due soon". */
    public const PMS_DUE_SOON_DAYS = 30;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'address',
        'city', 'state', 'zip_code', 'customer_type', 'segment',
        'preferred_contact', 'facebook_profile', 'profile_picture', 'last_visit',
        'is_active', 'notes', 'source',
    ];

    protected $appends = [
        'full_name',
        'name',
        'total_vehicles',
        'total_services',
        'total_spent',
        'average_service_cost',
        'last_service_date',
        'upcoming_services',
    ];

    protected $casts = [
        'last_visit' => 'datetime',
        'form_submitted_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function serviceRecords()
    {
        return $this->hasMany(ServiceRecord::class);
    }

    public function notes()
    {
        return $this->hasMany(CustomerNote::class);
    }

    /**
     * Relation alias for customer notes.
     *
     * NOTE: the `customers` table has a `notes` TEXT *column* (string), which
     * shadows property access to the notes() relation ($customer->notes returns
     * the column value, never the relation). Views/controllers that need the
     * CustomerNote records must use $customer->customerNotes instead.
     */
    public function customerNotes()
    {
        return $this->hasMany(CustomerNote::class);
    }

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getNameAttribute()
    {
        return $this->full_name;
    }

    public function getTotalVehiclesAttribute()
    {
        return $this->vehicles()->count();
    }

    public function getTotalServicesAttribute()
    {
        return $this->serviceRecords()->count();
    }

    public function getTotalSpentAttribute()
    {
        return $this->serviceRecords()->sum('total_cost');
    }

    public function getAverageServiceCostAttribute()
    {
        return $this->serviceRecords()->avg('total_cost') ?? 0;
    }

    public function getLastServiceDateAttribute()
    {
        $last = $this->serviceRecords()->latest('service_date')->first();
        return $last ? $last->service_date : null;
    }

    public function getUpcomingServicesAttribute()
    {
        return $this->serviceRecords()
            ->where('service_date', '>=', now())
            ->orderBy('service_date')
            ->get();
    }

    public function getAvatarAttribute()
    {
        if ($this->profile_picture) {
            return Storage::url($this->profile_picture);
        }
        
        // Generate initials avatar
        $name = $this->full_name;
        $initials = '';
        
        if (!empty($this->first_name)) {
            $initials .= strtoupper(substr($this->first_name, 0, 1));
        }
        if (!empty($this->last_name)) {
            $initials .= strtoupper(substr($this->last_name, 0, 1));
        }
        
        if (empty($initials)) {
            $initials = '?';
        }
        
        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&color=7F9CF5&background=EBF4FF&bold=true&size=128';
    }

    public function getHasProfilePictureAttribute()
    {
        return !is_null($this->profile_picture);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function latestQuotation()
    {
        return $this->hasOne(Quotation::class)->latestOfMany();
    }

    /**
     * Most recent repair order of any status — used as a visit indicator
     * when no ServiceRecord exists yet (a customer with a Repair Order has
     * obviously visited the shop).
     */
    public function latestInspection()
    {
        return $this->hasOne(VehicleInspection::class, 'customer_id')->latestOfMany('created_at');
    }

    public function inspections()
    {
        return $this->hasMany(VehicleInspection::class);
    }

    public function estimates()
    {
        return $this->hasMany(Estimate::class);
    }

    /**
     * Total value of everything connected to this customer:
     * Service Records + Repair Orders (computed) + unconverted Repair Quotations
     * + Invoices not tied to a quotation.
     *
     * Converted quotations (those with an `inspection_id`) are skipped because the
     * resulting Repair Order already carries their value — avoids double counting.
     */
    public function getConnectedTotalAttribute(): float
    {
        $total = (float) ($this->service_records_sum_final_amount
            ?? $this->serviceRecords()->sum('final_amount'));

        // Repair Orders — the value is computed from their line items.
        $inspections = $this->relationLoaded('inspections')
            ? $this->inspections
            : $this->inspections()->with('appointment')->get();
        foreach ($inspections as $ro) {
            $total += $ro->repair_total;
        }

        // Quotations never promoted to a Repair Order.
        $total += (float) Estimate::where('customer_id', $this->id)
            ->whereNull('inspection_id')
            ->sum('total_amount');

        // Invoices not linked to a quotation (avoid counting converted jobs twice).
        $total += (float) Invoice::where('customer_id', $this->id)
            ->whereNull('estimate_id')
            ->sum('total_amount');

        return round($total, 2);
    }

    /**
     * Most recent PREVENTIVE MAINTENANCE (PMS) repair order that has been
     * released from the workshop. The 6-month PMS countdown starts on its
     * workshop_released_at date.
     *
     * PMS-ness is detected by keyword (see VehicleInspection::PMS_KEYWORDS:
     * pms / preventive / change oil / oil change / tune up ...) across the
     * service type + job description text — so a released aircon/repair RO
     * must NOT start the next-PMS clock.
     */
    public function latestReleasedInspection()
    {
        return $this->hasOne(VehicleInspection::class, 'customer_id')
            ->whereIn('repair_status', ['released', 'paid'])
            ->pmsJobs()
            ->latestOfMany('workshop_released_at');
    }

    /**
     * Date the next PMS (Preventive Maintenance Service) is due —
     * 6 months after the last released repair order. Null when the customer
     * has no released repair order yet.
     */
    public function getPmsDueDateAttribute(): ?\Carbon\Carbon
    {
        $released = $this->relationLoaded('latestReleasedInspection')
            ? $this->latestReleasedInspection
            : $this->latestReleasedInspection()->first();

        $releasedAt = $released?->workshop_released_at;

        return $releasedAt ? $releasedAt->copy()->addMonths(self::PMS_INTERVAL_MONTHS) : null;
    }

    /** Signed days until the next PMS is due (negative = overdue). Null if never released. */
    public function getPmsDaysUntilAttribute(): ?int
    {
        $due = $this->pms_due_date;

        return $due ? (int) now()->startOfDay()->diffInDays($due->copy()->startOfDay(), false) : null;
    }

    /**
     * PMS badge state for the customer list.
     * Returns null when there is no released repair order to count from.
     */
    public function getPmsStatusAttribute(): ?array
    {
        $days = $this->pms_days_until;

        if (is_null($days)) {
            return null;
        }

        if ($days < 0) {
            $months = intdiv(abs($days), 30);

            return [
                'state' => 'overdue',
                'label' => $months > 0 ? "PMS {$months}mo overdue" : 'PMS overdue',
                'icon' => 'fa-triangle-exclamation',
            ];
        }

        if ($days <= self::PMS_DUE_SOON_DAYS) {
            return [
                'state' => 'due',
                'label' => $days === 0 ? 'PMS due today' : "PMS due in {$days}d",
                'icon' => 'fa-bell',
            ];
        }

        $months = (int) ceil($days / 30);

        return [
            'state' => 'ok',
            'label' => "PMS in {$months}mo",
            'icon' => 'fa-wrench',
        ];
    }

    public function getPmsDueDateLabelAttribute(): ?string
    {
        $due = $this->pms_due_date;

        return $due ? $due->format('M j, Y') : null;
    }

    /**
     * Get the portal user associated with this customer.
     */
    public function portalUser()
    {
        return $this->hasOne(PortalUser::class, 'customer_id');
    }
}
