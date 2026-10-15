<?php

namespace App\Queries;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;

class TeacherIndexQuery
{
    /** @param array<string, mixed> $filters */
    public function build(array $filters): Builder
    {
        return Teacher::query()
            ->when($filters['q'] ?? null, function (Builder $query, string $search): void {
                foreach (explode(' ', $search) as $term) {
                    $query->where(function (Builder $query) use ($term): void {
                        $like = '%'.$term.'%';

                        $query
                            ->where('first_name', 'like', $like)
                            ->orWhere('last_names', 'like', $like)
                            ->orWhere('identity_document', 'like', $like)
                            ->orWhere('email', 'like', $like)
                            ->orWhere('phone', 'like', $like)
                            ->orWhere('locality', 'like', $like)
                            ->orWhere('postal_code', 'like', $like)
                            ->orWhere('province', 'like', $like)
                            ->orWhere('country', 'like', $like)
                            ->orWhere('internal_notes', 'like', $like)
                            ->orWhereHas('subjects', fn (Builder $query) => $query->where('name', 'like', $like));
                    });
                }
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['subject_id'] ?? null, fn (Builder $query, int $subjectId) => $query->whereHas(
                'subjects',
                fn (Builder $query) => $query->whereKey($subjectId),
            ))
            ->when($filters['modality'] ?? null, fn (Builder $query, string $modality) => $query->whereHas(
                'availabilities',
                fn (Builder $query) => $query->where('modality', $modality),
            ))
            ->when(array_key_exists('creates_content', $filters) && $filters['creates_content'] !== null, fn (Builder $query) => $query->where(
                'creates_content',
                (bool) $filters['creates_content'],
            ))
            ->when(array_key_exists('updates_content', $filters) && $filters['updates_content'] !== null, fn (Builder $query) => $query->where(
                'updates_content',
                (bool) $filters['updates_content'],
            ))
            ->orderBy('last_names')
            ->orderBy('first_name')
            ->orderBy('id');
    }
}
