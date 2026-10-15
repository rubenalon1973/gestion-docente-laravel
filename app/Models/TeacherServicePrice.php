<?php

namespace App\Models;

use Database\Factories\TeacherServicePriceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherServicePrice extends Model
{
    /** @use HasFactory<TeacherServicePriceFactory> */
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'service_id',
        'subject_id',
        'pricing_unit_id',
        'modality',
        'net_amount',
        'currency',
        'valid_from',
        'valid_until',
    ];

    protected function casts(): array
    {
        return [
            'net_amount' => 'decimal:2',
            'valid_from' => 'date',
            'valid_until' => 'date',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function pricingUnit(): BelongsTo
    {
        return $this->belongsTo(PricingUnit::class);
    }
}
