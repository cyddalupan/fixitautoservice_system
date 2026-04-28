<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Archive extends Model
{
    protected $fillable = [
        'archivable_id',
        'archivable_type',
        'source_module',
        'archived_by',
        'original_data',
        'notes',
        'archived_at',
        'restored_at',
    ];

    protected $casts = [
        'original_data' => 'array',
        'archived_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    public function archivable(): MorphTo
    {
        return $this->morphTo();
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    /**
     * Scope: filter by source module.
     */
    public function scopeModule($query, $module)
    {
        return $query->where('source_module', $module);
    }

    /**
     * Scope: filter by archived date range.
     */
    public function scopeArchivedBetween($query, $from, $to)
    {
        return $query->whereBetween('archived_at', [$from, $to]);
    }

    /**
     * Scope: filter by archived year.
     */
    public function scopeArchivedInYear($query, $year)
    {
        return $query->whereYear('archived_at', $year);
    }

    /**
     * Scope: filter by archived month + year.
     */
    public function scopeArchivedInMonth($query, $year, $month)
    {
        return $query->whereYear('archived_at', $year)->whereMonth('archived_at', $month);
    }

    /**
     * Scope: search across original_data JSON (customer name, invoice number, plate number).
     */
    public function scopeSearch($query, $term)
    {
        if (empty($term)) return $query;

        $term = strtolower($term);

        // Use whereRaw with JSON_SEARCH for MySQL JSON column
        return $query->where(function ($q) use ($term) {
            $q->whereRaw('LOWER(JSON_EXTRACT(original_data, "$.customer_name")) LIKE ?', ["%{$term}%"])
              ->orWhereRaw('LOWER(JSON_EXTRACT(original_data, "$.customer_first_name")) LIKE ?', ["%{$term}%"])
              ->orWhereRaw('LOWER(JSON_EXTRACT(original_data, "$.customer_last_name")) LIKE ?', ["%{$term}%"])
              ->orWhereRaw('LOWER(JSON_EXTRACT(original_data, "$.plate_number")) LIKE ?', ["%{$term}%"])
              ->orWhereRaw('LOWER(JSON_EXTRACT(original_data, "$.invoice_number")) LIKE ?', ["%{$term}%"])
              ->orWhereRaw('LOWER(JSON_EXTRACT(original_data, "$.estimate_number")) LIKE ?', ["%{$term}%"])
              ->orWhereRaw('LOWER(JSON_EXTRACT(original_data, "$.work_order_number")) LIKE ?', ["%{$term}%"])
              ->orWhereRaw('LOWER(JSON_EXTRACT(original_data, "$.reference_number")) LIKE ?', ["%{$term}%"])
              ->orWhere('source_module', 'LIKE', "%{$term}%");
        });
    }
}
