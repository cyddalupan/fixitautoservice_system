<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'specialization',
        'phone',
        'address',
        'employee_id',
        'hire_date',
        'skills',
        'certifications',
        'hourly_rate',
        'years_experience',
        'is_active',
        'employment_type',
        'shift_schedule',
        'emergency_contact_name',
        'emergency_contact_phone',
        'can_train_others',
        'is_team_lead',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'hire_date' => 'date',
            'skills' => 'array',
            'certifications' => 'array',
            'hourly_rate' => 'decimal:2',
            'years_experience' => 'integer',
            'is_active' => 'boolean',
            'can_train_others' => 'boolean',
            'is_team_lead' => 'boolean',
        ];
    }

    public function technicianServiceRecords()
    {
        return $this->hasMany(ServiceRecord::class, 'technician_id');
    }

    public function advisorServiceRecords()
    {
        return $this->hasMany(ServiceRecord::class, 'service_advisor_id');
    }

    public function customerNotes()
    {
        return $this->hasMany(CustomerNote::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isManager()
    {
        return $this->role === 'manager';
    }

    public function isServiceAdvisor()
    {
        return $this->role === 'service_advisor';
    }

    public function isTechnician()
    {
        return $this->role === 'technician';
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    public function getRoleBadgeColorAttribute()
    {
        return match($this->role) {
            'admin' => 'danger',
            'manager' => 'warning',
            'service_advisor' => 'info',
            'technician' => 'primary',
            'customer' => 'success',
            default => 'secondary',
        };
    }

    public function getFormattedSkillsAttribute()
    {
        if (!$this->skills || !is_array($this->skills)) {
            return [];
        }
        
        return array_map(function($skill) {
            return '<span class="badge bg-primary">' . htmlspecialchars($skill) . '</span>';
        }, $this->skills);
    }

    public function getFormattedCertificationsAttribute()
    {
        if (!$this->certifications || !is_array($this->certifications)) {
            return [];
        }
        
        return array_map(function($cert) {
            return '<span class="badge bg-success">' . htmlspecialchars($cert) . '</span>';
        }, $this->certifications);
    }

    public function getCompletedServicesCountAttribute()
    {
        return $this->technicianServiceRecords()
            ->where('service_status', 'completed')
            ->count();
    }

    public function getTotalLaborHoursAttribute()
    {
        // This would need to be calculated based on actual time tracking
        // For now, we'll return a placeholder
        return $this->completed_services_count * 2; // Assuming 2 hours per service
    }

    public function getTotalLaborRevenueAttribute()
    {
        return $this->getTotalLaborHoursAttribute() * $this->hourly_rate;
    }

    public function getEmploymentTypeBadgeColorAttribute()
    {
        return match($this->employment_type) {
            'full_time' => 'success',
            'part_time' => 'info',
            'contract' => 'warning',
            'temporary' => 'secondary',
            default => 'light',
        };
    }

    public function getFormattedEmploymentTypeAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->employment_type));
    }

    public function getExperienceLevelAttribute()
    {
        if (!$this->years_experience) {
            return 'Novice';
        }

        return match(true) {
            $this->years_experience < 1 => 'Trainee',
            $this->years_experience < 3 => 'Junior',
            $this->years_experience < 5 => 'Intermediate',
            $this->years_experience < 10 => 'Senior',
            default => 'Expert',
        };
    }

    public function getCanTrainOthersBadgeAttribute()
    {
        return $this->can_train_others 
            ? '<span class="badge bg-success">Trainer</span>'
            : '<span class="badge bg-secondary">Trainee</span>';
    }

    public function getTeamLeadBadgeAttribute()
    {
        return $this->is_team_lead 
            ? '<span class="badge bg-warning">Team Lead</span>'
            : '';
    }

    /**
     * Get all parts requests made by this technician.
     */
    public function partsRequests()
    {
        return $this->hasMany(PartsRequest::class, 'technician_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTechnicians($query)
    {
        return $query->where('role', 'technician');
    }

    public function scopeAdvisors($query)
    {
        return $query->where('role', 'service_advisor');
    }

    public function scopeManagers($query)
    {
        return $query->where('role', 'manager');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeCustomers($query)
    {
        return $query->where('role', 'customer');
    }
}