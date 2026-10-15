@csrf
@if ($service)
    @method('PUT')
@endif

<div class="form-grid">
    @if ($service)
        <div class="form-group-wide">
            <span class="form-label">Código interno</span>
            <p class="form-readonly-value">
                {{ $service->code }}
            </p>
            <p class="text-xs text-slate-500">El código es estable y no puede editarse.</p>
        </div>
    @endif

    <div class="form-group-wide">
        <label class="form-label" for="name">Nombre</label>
        <input class="form-control" id="name" name="name" type="text" maxlength="150"
            value="{{ old('name', $service?->name) }}" required>
    </div>

    <div class="form-group-wide">
        <label class="form-label" for="description">Descripción</label>
        <textarea class="form-control" id="description" name="description" maxlength="2000">{{ old('description', $service?->description) }}</textarea>
    </div>

    <div class="form-actions">
        <x-ui.button type="submit">{{ $submitLabel }}</x-ui.button>
        <x-ui.button href="{{ route('services.index') }}" variant="secondary">Cancelar</x-ui.button>
    </div>
</div>
