<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherSubjectRequest;
use App\Http\Requests\UpdateTeacherSubjectRequest;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class TeacherSubjectController extends Controller
{
    public function store(StoreTeacherSubjectRequest $request, Teacher $teacher): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($teacher, $validated): void {
            $subject = isset($validated['subject_id'])
                ? Subject::query()->findOrFail($validated['subject_id'])
                : Subject::create([
                    'name' => $validated['subject_name'],
                    'description' => $validated['subject_description'] ?? null,
                ]);

            $teacher->subjects()->attach($subject, [
                'details' => $validated['details'] ?? null,
            ]);
        });

        return redirect()
            ->route('teachers.show', $teacher)
            ->with('success', 'Materia asociada correctamente.');
    }

    public function update(
        UpdateTeacherSubjectRequest $request,
        Teacher $teacher,
        Subject $subject,
    ): RedirectResponse {
        $teacher->subjects()->updateExistingPivot($subject->id, $request->validated());

        return redirect()
            ->route('teachers.show', $teacher)
            ->with('success', 'Detalles de la materia actualizados correctamente.');
    }

    public function destroy(Teacher $teacher, Subject $subject): RedirectResponse
    {
        $teacher->subjects()->detach($subject);

        return redirect()
            ->route('teachers.show', $teacher)
            ->with('success', 'Materia desasociada correctamente.');
    }
}
