<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('services.index', [
            'services' => Service::query()->orderByDesc('is_active')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('services.create');
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Service::query()->create([
            ...$validated,
            'code' => $this->uniqueCode($validated['name']),
            'is_active' => true,
        ]);

        return redirect()->route('services.index')->with('success', 'Servicio creado correctamente.');
    }

    public function edit(Service $service): View
    {
        return view('services.edit', ['service' => $service]);
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $service->update($request->validated());

        return redirect()->route('services.index')->with('success', 'Servicio actualizado correctamente.');
    }

    public function updateStatus(Service $service): RedirectResponse
    {
        $service->update(['is_active' => ! $service->is_active]);

        return redirect()->route('services.index')->with(
            'success',
            $service->is_active ? 'Servicio activado correctamente.' : 'Servicio desactivado correctamente.',
        );
    }

    private function uniqueCode(string $name): string
    {
        $base = Str::snake(Str::ascii($name)) ?: 'service';
        $code = Str::limit($base, 50, '');
        $suffix = 2;

        while (Service::query()->where('code', $code)->exists()) {
            $ending = '_'.$suffix++;
            $code = Str::limit($base, 50 - strlen($ending), '').$ending;
        }

        return $code;
    }
}
