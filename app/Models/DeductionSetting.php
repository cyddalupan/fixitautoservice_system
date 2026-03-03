<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeductionSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'deduction_type',
        'deduction_name',
        'amount',
        'percentage',
        'is_percentage',
        'is_active',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:4',
        'is_percentage' => 'boolean',
        'is_active' => 'boolean',
    ];
}