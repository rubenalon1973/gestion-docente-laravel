<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherQualificationRequest;
use App\Http\Requests\UpdateTeacherQualificationRequest;
use App\Models\Qualification;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TeacherQualificationController extends Controller
{
    public function store(StoreTeacherQualificationRequest $request, Teacher $teacher): RedirectResponse
    {
        $teacher->qualifications()->create($request->validated());

        return redirect()
            ->route('teachers.show', $teacher)
            ->with('success', 'Formación académica añadida correctamente.');
    }

    public function edit(Teacher $teacher, Qualification $qualification): View
    {
        return view('teachers.qualifications.edit', [
            'teacher' => $teacher,
            'qualification' => $qualification,
        ]);
    }

    public function update(
        UpdateTeacherQualificationRequest $request,
        Teacher $teacher,
        Qualification $qualification,
    ): RedirectResponse {
        $qualification->update($request->validated());

        return redirect()
            ->route('teachers.show', $teacher)
            ->with('success', 'Formación académica actualizada correctamente.');
    }

    public function destroy(Teacher $teacher, Qualification $qualification): RedirectResponse
    {
        $qualification->delete();

        return redirect()
            ->route('teachers.show', $teacher)
            ->with('success', 'Formación académica eliminada correctamente.');
    }
}
