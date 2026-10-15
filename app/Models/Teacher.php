<?php

namespace App\Models;

use Database\Factories\TeacherFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    /** @use HasFactory<TeacherFactory> */
    use HasFactory;

    /** @var list<string> */
    public const STATUSES = [
        'active',
        'inactive',
        'archived',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    protected $fillable = [
        'first_name',
        'last_names',
        'identity_document',
        'email',
        'phone',
        'birth_date',
        'birth_place',
        'locality',
        'postal_code',
        'province',
        'country',
        'address',
        'years_of_experience',
        'creates_content',
        'updates_content',
        'status',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'years_of_experience' => 'integer',
            'creates_content' => 'boolean',
            'updates_content' => 'boolean',
        ];
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class)
            ->withPivot('details')
            ->withTimestamps();
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }

    public function qualifications(): HasMany
    {
        return $this->hasMany(Qualification::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function teacherTools(): HasMany
    {
        return $this->hasMany(TeacherTool::class);
    }

    public function teacherServicePrices(): HasMany
    {
        return $this->hasMany(TeacherServicePrice::class);
    }
}
