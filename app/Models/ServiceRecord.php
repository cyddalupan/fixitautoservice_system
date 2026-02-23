<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'customer_id',
        'service_date',
        'odometer_at_service',
        'service_type',
        'description',
        'labor_cost',
        'parts_cost',
        'total_cost',
        'tax_amount',
        'discount_amount',
        'final_amount',
        'payment_status',
        'service_status',
        'technician_id',
        'service_advisor_id',
        'work_order_number',
        'diagnosis',
        'recommendations',
        'parts_used',
        'inspection_results',
        'photos',
        'warranty_work',
        'warranty_type',
        'next_service_date',
        'next_service_odometer',
        'customer_feedback',
        'customer_rating',
    ];

    protected $casts = [
        'service_date' => 'date',
        'odometer_at_service' => 'integer',
        'labor_cost' => 'decimal:2',
        'parts_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'warranty_work' => 'boolean',
        'next_service_date' => 'date',
        'next_service_odometer' => 'integer',
        'customer_rating' => 'integer',
        'inspection_results' => 'array',
        'photos' => 'array',
        'parts_used' => 'array',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function serviceAdvisor()
    {
        return $this->belongsTo(User::class, 'service_advisor_id');
    }

    public function getServiceDetailsAttribute()
    {
        $details = [];
        
        if ($this->parts_used) {
            $details[] = 'Parts: ' . implode(', ', $this->parts_used);
        }
        
        if ($this->diagnosis) {
            $details[] = 'Diagnosis: ' . substr($this->diagnosis, 0, 100) . '...';
        }
        
        if ($this->recommendations) {
            $details[] = 'Recommendations: ' . substr($this->recommendations, 0, 100) . '...';
        }
        
        return implode(' | ', $details);
    }

    public function getStatusBadgeColorAttribute()
    {
        return match($this->service_status) {
            'scheduled' => 'info',
            'in_progress' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getPaymentBadgeColorAttribute()
    {
        return match($this->payment_status) {
            'pending' => 'warning',
            'partial' => 'info',
            'paid' => 'success',
            'overdue' => 'danger',
            default => 'secondary',
        };
    }

    public function getCustomerRatingStarsAttribute()
    {
        if (!$this->customer_rating) {
            return null;
        }
        
        $stars = '';
        for ($i = 1; $i <= 5; $i++) {
            $stars .= $i <= $this->customer_rating ? '★' : '☆';
        }
        
        return $stars;
    }

    public function scopeCompleted($query)
    {
        return $query->where('service_status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('service_status', '!=', 'completed');
    }

    public function scopeOverdue($query)
    {
        return $query->where('payment_status', 'overdue');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('service_date', now()->month)
                    ->whereYear('service_date', now()->year);
    }

    public function scopeLastMonth($query)
    {
        return $query->whereMonth('service_date', now()->subMonth()->month)
                    ->whereYear('service_date', now()->subMonth()->year);
    }
}