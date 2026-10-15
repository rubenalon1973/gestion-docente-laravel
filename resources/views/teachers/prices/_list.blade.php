<section class="section-stack">
    <h3 class="text-base font-semibold text-slate-900">{{ $title }}</h3>

    @if ($prices->isEmpty())
        <p class="empty-state">{{ $emptyMessage }}</p>
    @else
        <x-ui.table class="responsive-card-table responsive-price-table">
            <thead>
                <tr>
                    <th>Ámbito</th>
                    <th>Servicio</th>
                    <th>Unidad</th>
                    <th>Importe</th>
                    <th>Materia</th>
                    <th>Modalidad</th>
                    <th>Vigencia</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($prices as $price)
                    @php($isGeneral = $price->subject_id === null && $price->modality === 'all')
                    @php($statusLabel = match ($title) {
                        'Vigentes' => 'Vigente',
                        'Programadas' => 'Programada',
                        default => 'Histórica',
                    })
                    <tr>
                        <td data-label="Ámbito" data-field="scope">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $isGeneral ? 'bg-blue-100 text-blue-800' : 'bg-violet-100 text-violet-800' }}">
                                {{ $isGeneral ? 'General' : 'Específica' }}
                            </span>
                        </td>
                        <td data-label="Servicio" data-field="service">
                            <span class="font-medium text-slate-900">{{ $price->service->name }}</span>
                            @unless ($price->service->is_active)
                                <span class="block text-xs text-slate-500">Servicio inactivo</span>
                            @endunless
                        </td>
                        <td data-label="Unidad" data-field="unit">
                            {{ $price->pricingUnit->name }}
                            @unless ($price->pricingUnit->is_active)
                                <span class="block text-xs text-slate-500">Unidad inactiva</span>
                            @endunless
                        </td>
                        <td class="whitespace-nowrap font-semibold text-slate-900" data-label="Importe" data-field="amount">
                            {{ number_format((float) $price->net_amount, 2, ',', '.') }} €
                        </td>
                        <td data-label="Materia" data-field="subject">{{ $price->subject?->name ?? 'Todas' }}</td>
                        <td data-label="Modalidad" data-field="modality">{{ match ($price->modality) {
                            'in_person' => 'Presencial',
                            'online' => 'En línea',
                            'all' => 'Ambas',
                        } }}</td>
                        <td class="whitespace-nowrap" data-label="Vigencia" data-field="validity">
                            {{ $price->valid_from->format('d/m/Y') }}–{{ $price->valid_until?->format('d/m/Y') ?? 'Sin fecha de fin' }}
                        </td>
                        <td class="responsive-only" data-label="Estado" data-field="status">{{ $statusLabel }}</td>
                        <td data-label="Acción" data-field="action">
                            @if ($allowClose)
                                <form class="responsive-price-close flex min-w-56 items-end gap-2" method="POST"
                                    action="{{ route('teachers.prices.close', [$teacher, $price]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <div class="form-group">
                                        <label class="form-label" for="close_valid_until_{{ $price->id }}">Último día</label>
                                        <input class="form-control" id="close_valid_until_{{ $price->id }}" name="valid_until"
                                            type="date" min="{{ $price->valid_from->toDateString() }}"
                                            value="{{ $price->valid_from->isAfter(today()) ? $price->valid_from->toDateString() : today()->toDateString() }}" required>
                                    </div>
                                    <x-ui.button type="submit" variant="secondary">Cerrar</x-ui.button>
                                </form>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
    @endif
</section>
