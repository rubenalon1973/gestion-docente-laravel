<?php

namespace App\Models;

use Database\Factories\TeacherToolFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherTool extends Model
{
    /** @use HasFactory<TeacherToolFactory> */
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'category',
        'tool_name',
        'level',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
