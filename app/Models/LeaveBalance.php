<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type',
        'total_allocated',
        'used',
        'remaining',
        'year',
    ];

    protected $casts = [
        'total_allocated' => 'decimal:2',
        'used' => 'decimal:2',
        'remaining' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}