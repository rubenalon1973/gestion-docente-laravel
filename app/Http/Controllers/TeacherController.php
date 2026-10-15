<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterTeachersRequest;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Http\Requests\UpdateTeacherStatusRequest;
use App\Models\PricingUnit;
use App\Models\Service;
use App\Models\Subject;
use App\Models\Teacher;
use App\Queries\TeacherIndexQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(FilterTeachersRequest $request, TeacherIndexQuery $teacherIndexQuery): View
    {
        $filters = $request->validated();
        $teachers = $teacherIndexQuery->build($filters)
            ->paginate(15)
            ->withQueryString();

        $subjects = Subject::query()
            ->has('teachers')
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name']);

        $hasFilters = collect($filters)
            ->contains(fn ($value) => $value !== null && $value !== '');

        return view('teachers.index', [
            'teachers' => $teachers,
            'subjects' => $subjects,
            'filters' => $filters,
            'hasFilters' => $hasFilters,
        ]);
    }

    public function create(): View
    {
        return view('teachers.create');
    }

    public function store(StoreTeacherRequest $request): RedirectResponse
    {
        $teacher = Teacher::create($request->validated());

        return redirect()
            ->route('teachers.show', $teacher)
            ->with('success', 'Profesor creado correctamente.');
    }

    public function show(Teacher $teacher): View
    {
        $teacher->load([
            'subjects' => fn ($query) => $query
                ->orderBy('name')
                ->orderBy('subjects.id'),
            'availabilities' => fn ($query) => $query
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->orderBy('id'),
            'qualifications' => fn ($query) => $query
                ->orderByDesc('completion_year')
                ->orderBy('title')
                ->orderBy('id'),
            'teacherTools' => fn ($query) => $query
                ->orderBy('category')
                ->orderBy('tool_name')
                ->orderBy('id'),
            'documents' => fn ($query) => $query
                ->latest()
                ->orderByDesc('id'),
            'teacherServicePrices' => fn ($query) => $query
                ->with(['service', 'subject', 'pricingUnit'])
                ->orderByDesc('valid_from')
                ->orderByDesc('id'),
        ]);

        $today = today();
        $scheduledPrices = $teacher->teacherServicePrices
            ->filter(fn ($price) => $price->valid_from->isAfter($today));
        $historicalPrices = $teacher->teacherServicePrices
            ->filter(fn ($price) => $price->valid_until?->isBefore($today) ?? false);
        $currentPrices = $teacher->teacherServicePrices
            ->reject(fn ($price) => $price->valid_from->isAfter($today))
            ->reject(fn ($price) => $price->valid_until?->isBefore($today) ?? false);

        $availableSubjects = Subject::query()
            ->whereDoesntHave(
                'teachers',
                fn ($query) => $query->whereKey($teacher->id),
            )
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name']);

        $activeServices = Service::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name']);

        $activePricingUnits = PricingUnit::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name']);

        return view('teachers.show', [
            'teacher' => $teacher,
            'availableSubjects' => $availableSubjects,
            'activeServices' => $activeServices,
            'activePricingUnits' => $activePricingUnits,
            'currentPrices' => $currentPrices,
            'scheduledPrices' => $scheduledPrices,
            'historicalPrices' => $historicalPrices,
        ]);
    }

    public function edit(Teacher $teacher): View
    {
        return view('teachers.edit', ['teacher' => $teacher]);
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher): RedirectResponse
    {
        $teacher->update($request->validated());

        return redirect()
            ->route('teachers.show', $teacher)
            ->with('success', 'Profesor actualizado correctamente.');
    }

    public function updateStatus(UpdateTeacherStatusRequest $request, Teacher $teacher): RedirectResponse
    {
        $teacher->update($request->validated());

        return redirect()
            ->route('teachers.show', $teacher)
            ->with('success', 'Estado actualizado correctamente.');
    }
}
