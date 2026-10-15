@csrf

<div class="form-grid">
    <div class="form-group">
        <label class="form-label" for="price_service_id">Servicio</label>
        <select class="form-control" id="price_service_id" name="service_id" required>
            <option value="">Selecciona un servicio</option>
            @foreach ($activeServices as $service)
                <option value="{{ $service->id }}" @selected((string) old('service_id') === (string) $service->id)>
                    {{ $service->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label class="form-label" for="price_pricing_unit_id">Unidad</label>
        <select class="form-control" id="price_pricing_unit_id" name="pricing_unit_id" required>
            <option value="">Selecciona una unidad</option>
            @foreach ($activePricingUnits as $unit)
                <option value="{{ $unit->id }}" @selected((string) old('pricing_unit_id') === (string) $unit->id)>
                    {{ $unit->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label class="form-label" for="price_net_amount">Importe en euros</label>
        <input class="form-control" id="price_net_amount" name="net_amount" type="number" min="0.01" step="0.01"
            inputmode="decimal" value="{{ old('net_amount') }}" required>
    </div>

    <div class="form-group">
        <label class="form-label" for="price_subject_id">Materia</label>
        <select class="form-control" id="price_subject_id" name="subject_id">
            <option value="">Todas las materias</option>
            @foreach ($teacher->subjects as $subject)
                <option value="{{ $subject->id }}" @selected((string) old('subject_id') === (string) $subject->id)>
                    {{ $subject->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label class="form-label" for="price_modality">Modalidad</label>
        <select class="form-control" id="price_modality" name="modality" required>
            <option value="all" @selected(old('modality', 'all') === 'all')>Ambas modalidades</option>
            <option value="in_person" @selected(old('modality') === 'in_person')>Presencial</option>
            <option value="online" @selected(old('modality') === 'online')>En línea</option>
        </select>
    </div>

    <div class="form-group">
        <label class="form-label" for="price_valid_from">Inicio de vigencia</label>
        <input class="form-control" id="price_valid_from" name="valid_from" type="date"
            value="{{ old('valid_from', today()->toDateString()) }}" required>
    </div>

    <div class="form-group">
        <label class="form-label" for="price_valid_until">Fin de vigencia</label>
        <input class="form-control" id="price_valid_until" name="valid_until" type="date"
            value="{{ old('valid_until') }}">
        <p class="text-xs text-slate-500">Opcional. Las fechas de inicio y fin están incluidas.</p>
    </div>

    <p class="self-end pb-3 text-sm text-slate-500">
        Sin materia y con ambas modalidades se considera una tarifa general. Cualquier restricción crea una tarifa específica.
    </p>

    <div class="form-actions">
        <x-ui.button type="submit">Añadir tarifa</x-ui.button>
    </div>
</div>
