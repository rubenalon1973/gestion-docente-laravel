@csrf

<div class="form-grid">
    @if ($availability === null)
        <fieldset class="form-group-wide">
            <legend class="form-label">Días de la semana</legend>
            <div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([1 => 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $dayNumber => $dayName)
                    <label class="checkbox-label" for="day_{{ $dayNumber }}">
                <input class="checkbox-control" id="day_{{ $dayNumber }}" name="days[]" type="checkbox" value="{{ $dayNumber }}"
                    @checked(in_array((string) $dayNumber, old('days', []), true))>
                {{ $dayName }}
            </label>
        @endforeach
            </div>
        </fieldset>
    @else
        <div class="form-group">
        <label class="form-label" for="day_of_week">Día de la semana</label>
        <select class="form-control" id="day_of_week" name="day_of_week" required>
            @foreach ([1 => 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $dayNumber => $dayName)
                <option value="{{ $dayNumber }}" @selected((int) old('day_of_week', $availability->day_of_week) === $dayNumber)>
                    {{ $dayName }}
                </option>
            @endforeach
        </select>
        </div>
    @endif
    <div class="form-group">
    <label class="form-label" for="start_time">Hora de inicio</label>
    <input class="form-control" id="start_time" name="start_time" type="time" required
        value="{{ old('start_time', $availability ? substr($availability->start_time, 0, 5) : '') }}">
    </div>
    <div class="form-group">
    <label class="form-label" for="end_time">Hora de fin</label>
    <input class="form-control" id="end_time" name="end_time" type="time" required
        value="{{ old('end_time', $availability ? substr($availability->end_time, 0, 5) : '') }}">
    </div>
    <div class="form-group">
    <label class="form-label" for="modality">Modalidad</label>
    <select class="form-control" id="modality" name="modality" required>
        <option value="online" @selected(old('modality', $availability?->modality) === 'online')>En línea</option>
        <option value="in_person" @selected(old('modality', $availability?->modality) === 'in_person')>Presencial</option>
    </select>
    </div>
    <div class="form-actions">
        <x-ui.button type="submit">{{ $submitLabel }}</x-ui.button>
    </div>
</div>
