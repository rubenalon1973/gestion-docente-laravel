<?php

namespace App\Models;

use Database\Factories\QualificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Qualification extends Model
{
    /** @use HasFactory<QualificationFactory> */
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'title',
        'institution',
        'completion_year',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'completion_year' => 'integer',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
