<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Blueprint-compliant Service catalog model.
 *
 * Maps to the SIMPLE blueprint `services` table:
 *   id, name, description, default_price (decimal), category, is_active
 * Source of truth: Fixit Blueprint card + fixit-remake-feature-plan.md.
 * Intentionally SEPARATE from the legacy `Service` model (old app, which
 * is mis-mapped to the `appointments` table).
 */
class BlueprintService extends Model
{
    use HasFactory;

    protected $table = 'services';

    protected $fillable = [
        'name',
        'description',
        'default_price',
        'category',
        'is_active',
    ];

    protected $casts = [
        'default_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
