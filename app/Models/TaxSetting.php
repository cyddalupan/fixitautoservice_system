<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_type',
        'tax_name',
        'rate',
        'minimum_income',
        'maximum_income',
        'is_active',
        'effective_date',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'minimum_income' => 'decimal:2',
        'maximum_income' => 'decimal:2',
        'is_active' => 'boolean',
        'effective_date' => 'date',
    ];
}