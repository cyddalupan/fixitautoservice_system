<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'user_id',
        'note_type',
        'content',
        'is_important',
        'requires_follow_up',
        'follow_up_date',
        'follow_up_completed',
        'tags',
    ];

    protected $casts = [
        'is_important' => 'boolean',
        'requires_follow_up' => 'boolean',
        'follow_up_date' => 'date',
        'follow_up_completed' => 'boolean',
        'tags' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getNoteTypeColorAttribute()
    {
        return match($this->note_type) {
            'general' => 'secondary',
            'preference' => 'info',
            'complaint' => 'danger',
            'compliment' => 'success',
            'follow_up' => 'warning',
            'reminder' => 'primary',
            default => 'light',
        };
    }

    public function getNoteTypeIconAttribute()
    {
        return match($this->note_type) {
            'general' => '📝',
            'preference' => '⭐',
            'complaint' => '⚠️',
            'compliment' => '👍',
            'follow_up' => '🔔',
            'reminder' => '⏰',
            default => '📄',
        };
    }

    public function getFormattedTagsAttribute()
    {
        if (!$this->tags || !is_array($this->tags)) {
            return [];
        }
        
        return array_map(function($tag) {
            return '<span class="badge bg-info">' . htmlspecialchars($tag) . '</span>';
        }, $this->tags);
    }

    public function getFollowUpStatusAttribute()
    {
        if (!$this->requires_follow_up) {
            return 'No follow-up required';
        }
        
        if ($this->follow_up_completed) {
            return 'Follow-up completed';
        }
        
        if ($this->follow_up_date) {
            $daysUntil = now()->diffInDays($this->follow_up_date, false);
            
            if ($daysUntil < 0) {
                return 'Overdue by ' . abs($daysUntil) . ' days';
            } elseif ($daysUntil == 0) {
                return 'Due today';
            } else {
                return 'Due in ' . $daysUntil . ' days';
            }
        }
        
        return 'Follow-up required (no date set)';
    }

    public function getFollowUpStatusColorAttribute()
    {
        if (!$this->requires_follow_up) {
            return 'secondary';
        }
        
        if ($this->follow_up_completed) {
            return 'success';
        }
        
        if ($this->follow_up_date) {
            $daysUntil = now()->diffInDays($this->follow_up_date, false);
            
            if ($daysUntil < 0) {
                return 'danger';
            } elseif ($daysUntil <= 2) {
                return 'warning';
            } else {
                return 'info';
            }
        }
        
        return 'warning';
    }

    public function scopeImportant($query)
    {
        return $query->where('is_important', true);
    }

    public function scopeRequiresFollowUp($query)
    {
        return $query->where('requires_follow_up', true)
                    ->where('follow_up_completed', false);
    }

    public function scopeOverdueFollowUp($query)
    {
        return $query->where('requires_follow_up', true)
                    ->where('follow_up_completed', false)
                    ->where('follow_up_date', '<', now());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('note_type', $type);
    }

    public function scopeWithTags($query, array $tags)
    {
        return $query->whereJsonContains('tags', $tags);
    }
}