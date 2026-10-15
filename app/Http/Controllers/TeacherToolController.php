<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherToolRequest;
use App\Http\Requests\UpdateTeacherToolRequest;
use App\Models\Teacher;
use App\Models\TeacherTool;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TeacherToolController extends Controller
{
    public function store(StoreTeacherToolRequest $request, Teacher $teacher): RedirectResponse
    {
        $teacher->teacherTools()->create($request->validated());

        return redirect()
            ->route('teachers.show', $teacher)
            ->with('success', 'Programa o herramienta añadida correctamente.');
    }

    public function edit(Teacher $teacher, TeacherTool $teacherTool): View
    {
        return view('teachers.tools.edit', [
            'teacher' => $teacher,
            'teacherTool' => $teacherTool,
        ]);
    }

    public function update(
        UpdateTeacherToolRequest $request,
        Teacher $teacher,
        TeacherTool $teacherTool,
    ): RedirectResponse {
        $teacherTool->update($request->validated());

        return redirect()
            ->route('teachers.show', $teacher)
            ->with('success', 'Programa o herramienta actualizado correctamente.');
    }

    public function destroy(Teacher $teacher, TeacherTool $teacherTool): RedirectResponse
    {
        $teacherTool->delete();

        return redirect()
            ->route('teachers.show', $teacher)
            ->with('success', 'Programa o herramienta eliminado correctamente.');
    }
}
