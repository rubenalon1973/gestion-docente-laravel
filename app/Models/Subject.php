<?php

namespace App\Models;

use Database\Factories\SubjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    /** @use HasFactory<SubjectFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class)
            ->withPivot('details')
            ->withTimestamps();
    }

    public function teacherServicePrices(): HasMany
    {
        return $this->hasMany(TeacherServicePrice::class);
    }
}
