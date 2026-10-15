<?php

namespace App\Models;

use Database\Factories\PricingUnitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingUnit extends Model
{
    /** @use HasFactory<PricingUnitFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function teacherServicePrices(): HasMany
    {
        return $this->hasMany(TeacherServicePrice::class);
    }
}
