<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherAvailabilityRequest;
use App\Http\Requests\UpdateTeacherAvailabilityRequest;
use App\Models\Availability;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TeacherAvailabilityController extends Controller
{
    public function store(StoreTeacherAvailabilityRequest $request, Teacher $teacher): RedirectResponse
    {
        $validated = $request->validated();
        $days = $validated['days'];
        unset($validated['days']);

        DB::transaction(function () use ($teacher, $days, $validated): void {
            $teacher->availabilities()->createMany(
                array_map(
                    fn (int $day): array => [...$validated, 'day_of_week' => $day],
                    $days,
                ),
            );
        });

        return redirect()
            ->route('teachers.show', $teacher)
            ->with('success', 'Disponibilidad añadida correctamente.');
    }

    public function edit(Teacher $teacher, Availability $availability): View
    {
        return view('teachers.availabilities.edit', [
            'teacher' => $teacher,
            'availability' => $availability,
        ]);
    }

    public function update(
        UpdateTeacherAvailabilityRequest $request,
        Teacher $teacher,
        Availability $availability,
    ): RedirectResponse {
        $availability->update($request->validated());

        return redirect()
            ->route('teachers.show', $teacher)
            ->with('success', 'Disponibilidad actualizada correctamente.');
    }

    public function destroy(Teacher $teacher, Availability $availability): RedirectResponse
    {
        $availability->delete();

        return redirect()
            ->route('teachers.show', $teacher)
            ->with('success', 'Disponibilidad eliminada correctamente.');
    }
}
