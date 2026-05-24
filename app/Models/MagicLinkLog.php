<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MagicLinkLog extends Model
{
    protected $fillable = [
        'customer_id',
        'email',
        'token',
        'status',
        'error_message',
    ];

    protected $casts = [
        'customer_id' => 'integer',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Scope: successful requests
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope: failed requests
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: filter by date range
     */
    public function scopeDateBetween($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}
