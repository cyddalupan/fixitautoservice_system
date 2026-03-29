<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'expense_number',
        'date',
        'category',
        'description',
        'amount',
        'vendor',
        'payment_method',
        'reference_number',
        'notes',
        'receipt_path',
        'user_id',
        'approved_by',
        'approved_at',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($expense) {
            if (empty($expense->expense_number)) {
                $expense->expense_number = 'EXP-' . date('Ymd') . '-' . str_pad(Expense::count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date', now()->month)
                    ->whereYear('date', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('date', now()->year);
    }

    // Helpers
    public function getFormattedAmountAttribute()
    {
        return '₱' . number_format($this->amount, 2);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'approved' => 'info',
            'rejected' => 'danger',
            'paid' => 'success',
        ];

        return '<span class="badge bg-' . ($badges[$this->status] ?? 'secondary') . '">' . ucfirst($this->status) . '</span>';
    }

    public function getCategoryBadgeAttribute()
    {
        $colors = [
            'office' => 'primary',
            'tools' => 'info',
            'utilities' => 'warning',
            'rent' => 'secondary',
            'supplies' => 'success',
            'vehicle' => 'danger',
            'other' => 'dark',
        ];

        return '<span class="badge bg-' . ($colors[$this->category] ?? 'secondary') . '">' . ucfirst($this->category) . '</span>';
    }
}
