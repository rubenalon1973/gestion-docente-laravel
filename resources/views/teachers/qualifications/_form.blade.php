@csrf

<div class="form-grid">
    <div class="form-group">
        <label class="form-label" for="title">Título o estudio</label>
        <input class="form-control" id="title" name="title" type="text" maxlength="200" required
            value="{{ old('title', $qualification?->title) }}">
    </div>
    <div class="form-group">
        <label class="form-label" for="institution">Institución</label>
        <input class="form-control" id="institution" name="institution" type="text" maxlength="200"
            value="{{ old('institution', $qualification?->institution) }}">
    </div>
    <div class="form-group">
        <label class="form-label" for="completion_year">Año de finalización</label>
        <input class="form-control" id="completion_year" name="completion_year" type="number" min="1900" max="{{ now()->year }}"
            value="{{ old('completion_year', $qualification?->completion_year) }}">
    </div>
    <div class="form-group-wide">
        <label class="form-label" for="notes">Notas</label>
        <textarea class="form-control" id="notes" name="notes">{{ old('notes', $qualification?->notes) }}</textarea>
    </div>
    <div class="form-actions">
        <x-ui.button type="submit">{{ $submitLabel }}</x-ui.button>
    </div>
</div>
