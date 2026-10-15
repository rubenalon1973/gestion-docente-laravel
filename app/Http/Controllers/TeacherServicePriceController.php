<?php

namespace App\Http\Controllers;

use App\Http\Requests\CloseTeacherServicePriceRequest;
use App\Http\Requests\StoreTeacherServicePriceRequest;
use App\Models\Teacher;
use App\Models\TeacherServicePrice;
use App\Services\TeacherServicePriceService;
use Illuminate\Http\RedirectResponse;

class TeacherServicePriceController extends Controller
{
    public function store(
        StoreTeacherServicePriceRequest $request,
        Teacher $teacher,
        TeacherServicePriceService $prices,
    ): RedirectResponse {
        $validated = $request->validated();

        $prices->create([
            ...$validated,
            'teacher_id' => $teacher->id,
            'subject_id' => $validated['subject_id'] ?? null,
            'currency' => 'EUR',
        ]);

        return redirect()
            ->route('teachers.show', $teacher)
            ->with('success', 'Tarifa añadida correctamente.');
    }

    public function close(
        CloseTeacherServicePriceRequest $request,
        Teacher $teacher,
        TeacherServicePrice $teacherServicePrice,
        TeacherServicePriceService $prices,
    ): RedirectResponse {
        $prices->update($teacherServicePrice, $request->validated());

        return redirect()
            ->route('teachers.show', $teacher)
            ->with('success', 'Vigencia de la tarifa cerrada correctamente.');
    }
}
