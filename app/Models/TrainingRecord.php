<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingRecord extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'technician_training_records';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'technician_id',
        'training_module_id',
        'status',
        'started_at',
        'completed_at',
        'score',
        'attempt_number',
        'notes',
        'approved_by',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'approved_at' => 'datetime',
            'score' => 'decimal:2',
        ];
    }

    /**
     * Get the technician for this training record.
     */
    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Get the training module for this record.
     */
    public function trainingModule()
    {
        return $this->belongsTo(TrainingModule::class, 'training_module_id');
    }

    /**
     * Get the approver for this training record.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include completed training.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include in-progress training.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope a query to only include assigned training.
     */
    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    /**
     * Check if the training is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the training is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if the training is assigned but not started.
     */
    public function isAssigned(): bool
    {
        return $this->status === 'assigned';
    }

    /**
     * Mark training as started.
     */
    public function markAsStarted(): bool
    {
        return $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark training as completed.
     */
    public function markAsCompleted(?float $score = null, ?string $notes = null): bool
    {
        return $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'score' => $score ?? $this->score,
            'notes' => $notes ?? $this->notes,
        ]);
    }

    /**
     * Get the training duration in hours.
     */
    public function getDurationHoursAttribute(): ?float
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInHours($this->completed_at);
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'completed' => 'success',
            'in_progress' => 'primary',
            'assigned' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Get the formatted status.
     */
    public function getFormattedStatusAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }
}