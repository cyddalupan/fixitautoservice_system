<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceDocument extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_name',
        'document_type',
        'document_number',
        'issuing_authority',
        'issue_date',
        'expiration_date',
        'renewal_date',
        'file_path',
        'file_name',
        'file_size',
        'notes',
        'is_active',
        'assigned_to',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'issue_date' => 'date',
        'expiration_date' => 'date',
        'renewal_date' => 'date',
        'file_size' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Document type constants
     */
    public const TYPE_SAFETY = 'safety';
    public const TYPE_ENVIRONMENTAL = 'environmental';
    public const TYPE_LICENSING = 'licensing';
    public const TYPE_INSURANCE = 'insurance';
    public const TYPE_TRAINING = 'training';
    public const TYPE_OTHER = 'other';

    /**
     * Get all document types
     */
    public static function getDocumentTypes(): array
    {
        return [
            self::TYPE_SAFETY => 'Safety',
            self::TYPE_ENVIRONMENTAL => 'Environmental',
            self::TYPE_LICENSING => 'Licensing',
            self::TYPE_INSURANCE => 'Insurance',
            self::TYPE_TRAINING => 'Training',
            self::TYPE_OTHER => 'Other',
        ];
    }

    /**
     * Get document type label
     */
    public function getDocumentTypeLabel(): string
    {
        return self::getDocumentTypes()[$this->document_type] ?? $this->document_type;
    }

    /**
     * Check if document is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expiration_date) {
            return false;
        }
        return $this->expiration_date->isPast();
    }

    /**
     * Check if document is expiring soon (within 30 days)
     */
    public function isExpiringSoon(): bool
    {
        if (!$this->expiration_date) {
            return false;
        }
        return $this->expiration_date->isFuture() && $this->expiration_date->diffInDays(now()) <= 30;
    }

    /**
     * Get days until expiration
     */
    public function getDaysUntilExpiration(): ?int
    {
        if (!$this->expiration_date) {
            return null;
        }
        return $this->expiration_date->diffInDays(now(), false) * -1; // Negative for past dates
    }

    /**
     * Get expiration status
     */
    public function getExpirationStatus(): string
    {
        if ($this->isExpired()) {
            return 'expired';
        } elseif ($this->isExpiringSoon()) {
            return 'expiring_soon';
        } else {
            return 'valid';
        }
    }

    /**
     * Get expiration status label
     */
    public function getExpirationStatusLabel(): string
    {
        return match($this->getExpirationStatus()) {
            'expired' => 'Expired',
            'expiring_soon' => 'Expiring Soon',
            'valid' => 'Valid',
            default => 'Unknown',
        };
    }

    /**
     * Get expiration status color
     */
    public function getExpirationStatusColor(): string
    {
        return match($this->getExpirationStatus()) {
            'expired' => 'danger',
            'expiring_soon' => 'warning',
            'valid' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Check if document needs renewal
     */
    public function needsRenewal(): bool
    {
        if (!$this->renewal_date) {
            return false;
        }
        return $this->renewal_date->isPast() || $this->renewal_date->diffInDays(now()) <= 60;
    }

    /**
     * Get file extension
     */
    public function getFileExtension(): ?string
    {
        if (!$this->file_name) {
            return null;
        }
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    /**
     * Get file icon based on extension
     */
    public function getFileIcon(): string
    {
        $extension = $this->getFileExtension();
        
        return match(strtolower($extension)) {
            'pdf' => 'file-pdf',
            'doc', 'docx' => 'file-word',
            'xls', 'xlsx' => 'file-excel',
            'jpg', 'jpeg', 'png', 'gif', 'bmp' => 'file-image',
            'zip', 'rar', '7z' => 'file-archive',
            default => 'file',
        };
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSize(): string
    {
        if (!$this->file_size) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->file_size;
        $unitIndex = 0;

        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Check if document has file
     */
    public function hasFile(): bool
    {
        return !empty($this->file_path) && !empty($this->file_name);
    }

    /**
     * Renew document
     */
    public function renew(array $data): bool
    {
        $this->fill($data);
        $this->renewal_date = null; // Reset renewal date
        return $this->save();
    }

    /**
     * Deactivate document
     */
    public function deactivate(): bool
    {
        $this->is_active = false;
        return $this->save();
    }

    /**
     * Activate document
     */
    public function activate(): bool
    {
        $this->is_active = true;
        return $this->save();
    }

    /**
     * Scope: Active documents
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Expired documents
     */
    public function scopeExpired($query)
    {
        return $query->whereDate('expiration_date', '<', now());
    }

    /**
     * Scope: Expiring soon (within 30 days)
     */
    public function scopeExpiringSoon($query)
    {
        return $query->whereDate('expiration_date', '>', now())
                    ->whereDate('expiration_date', '<=', now()->addDays(30));
    }

    /**
     * Scope: By document type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    /**
     * Scope: Needs renewal
     */
    public function scopeNeedsRenewal($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('renewal_date')
              ->orWhereDate('renewal_date', '<=', now()->addDays(60));
        });
    }

    /**
     * Relationship: Assigned user
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Relationship: Creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}