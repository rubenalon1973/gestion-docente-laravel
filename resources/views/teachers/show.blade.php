@extends('layouts.app')

@section('title', $teacher->first_name.' '.$teacher->last_names)

@section('content')
    <x-ui.page-header title="{{ $teacher->first_name }} {{ $teacher->last_names }}" description="Ficha completa del profesor.">
        <x-slot:actions>
            <x-ui.button href="{{ route('teachers.index') }}" variant="secondary">Volver al listado</x-ui.button>
            <x-ui.dropdown label="Exportar ficha">
                <x-ui.dropdown-link :href="route('teachers.export-one.pdf', $teacher)">PDF</x-ui.dropdown-link>
                <x-ui.dropdown-link :href="route('teachers.export-one.csv', $teacher)">CSV</x-ui.dropdown-link>
            </x-ui.dropdown>
            <x-ui.button href="{{ route('teachers.edit', $teacher) }}">Editar datos</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.form-errors />

    <div class="page-stack">
        <x-ui.card title="Datos personales">
            <dl class="definition-grid">
                <div><dt>Estado</dt><dd><x-ui.status-badge :status="$teacher->status" /></dd></div>
                <div><dt>DNI/NIE</dt><dd>{{ $teacher->identity_document ?: '—' }}</dd></div>
                <div><dt>Fecha de nacimiento</dt><dd>{{ $teacher->birth_date?->format('d/m/Y') ?: '—' }}</dd></div>
                <div><dt>Lugar de nacimiento</dt><dd>{{ $teacher->birth_place ?: '—' }}</dd></div>
            </dl>
        </x-ui.card>

        <x-ui.card title="Datos de contacto">
            <dl class="definition-grid">
                <div><dt>Correo electrónico</dt><dd>{{ $teacher->email ?: '—' }}</dd></div>
                <div><dt>Teléfono</dt><dd>{{ $teacher->phone ?: '—' }}</dd></div>
                <div><dt>Dirección</dt><dd class="whitespace-pre-wrap">{{ $teacher->address ?: '—' }}</dd></div>
                <div><dt>Localidad</dt><dd>{{ $teacher->locality ?: '—' }}</dd></div>
                <div><dt>Código postal</dt><dd>{{ $teacher->postal_code ?: '—' }}</dd></div>
                <div><dt>Provincia</dt><dd>{{ $teacher->province ?: '—' }}</dd></div>
                <div><dt>País</dt><dd>{{ $teacher->country ?: '—' }}</dd></div>
            </dl>
        </x-ui.card>

        <x-ui.card title="Perfil profesional">
            <dl class="definition-grid">
                <div><dt>Años de experiencia</dt><dd>{{ $teacher->years_of_experience ?? '—' }}</dd></div>
                <div><dt>Crea contenido</dt><dd>{{ $teacher->creates_content ? 'Sí' : 'No' }}</dd></div>
                <div><dt>Actualiza contenido</dt><dd>{{ $teacher->updates_content ? 'Sí' : 'No' }}</dd></div>
            </dl>
        </x-ui.card>

        <x-ui.card title="Gestión interna">
            <dl class="definition-grid">
                <div class="sm:col-span-2 xl:col-span-3">
                    <dt>Observaciones internas</dt>
                    <dd class="whitespace-pre-wrap">{{ $teacher->internal_notes ?: '—' }}</dd>
                </div>
            </dl>
        </x-ui.card>

    <x-ui.card title="Cambiar estado" description="Actualiza la situación actual del profesor.">
    <form method="POST" action="{{ route('teachers.update-status', $teacher) }}">
        @csrf
        @method('PATCH')
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
        <div class="form-group w-full sm:max-w-xs">
        <label class="form-label" for="status">Estado</label>
        <select class="form-control" id="status" name="status">
            <option value="active" @selected($teacher->status === 'active')>Activo</option>
            <option value="inactive" @selected($teacher->status === 'inactive')>Inactivo</option>
            <option value="archived" @selected($teacher->status === 'archived')>Archivado</option>
        </select>
        </div>
        <x-ui.button type="submit">Actualizar estado</x-ui.button>
        </div>
    </form>
    </x-ui.card>

    <x-ui.card title="Formación académica">
    <div class="section-stack">

    @if ($teacher->qualifications->isEmpty())
        <p>No hay formación académica registrada.</p>
    @else
        @foreach ($teacher->qualifications as $qualification)
            <section class="item-panel">
                <h3 class="font-semibold text-slate-900">{{ $qualification->title }}</h3>
                <dl class="mt-3 definition-grid">
                    <div>
                    <dt>Institución</dt>
                    <dd>{{ $qualification->institution ?: '—' }}</dd>
                    </div><div>
                    <dt>Año de finalización</dt>
                    <dd>{{ $qualification->completion_year ?: '—' }}</dd>
                    </div><div>
                    <dt>Notas</dt>
                    <dd>{{ $qualification->notes ?: '—' }}</dd>
                    </div>
                </dl>
                <div class="card-actions-footer action-row mt-4">
                    <x-ui.dropdown label="Acciones" table>
                        <x-ui.dropdown-link :href="route('teachers.qualifications.edit', [$teacher, $qualification])">Editar formación</x-ui.dropdown-link>
                        <form method="POST" action="{{ route('teachers.qualifications.destroy', [$teacher, $qualification]) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" role="menuitem" data-variant="danger">Eliminar formación</button>
                        </form>
                    </x-ui.dropdown>
                </div>
            </section>
        @endforeach
    @endif

    <h3 class="pt-2 text-base font-semibold text-slate-900">Añadir formación académica</h3>
    <form method="POST" action="{{ route('teachers.qualifications.store', $teacher) }}">
        @include('teachers.qualifications._form', [
            'qualification' => null,
            'submitLabel' => 'Añadir formación',
        ])
    </form>
    </div>
    </x-ui.card>

    <x-ui.card title="Programas e inteligencia artificial">
    <div class="section-stack">

    @foreach (['software' => 'Programas', 'ai' => 'Inteligencia artificial'] as $category => $categoryLabel)
        <h3 class="text-base font-semibold text-slate-900">{{ $categoryLabel }}</h3>

        @php($toolsInCategory = $teacher->teacherTools->where('category', $category))

        @if ($toolsInCategory->isEmpty())
            <p class="empty-state">No hay conocimientos registrados en esta categoría.</p>
        @else
            <ul class="divide-y divide-slate-200 rounded-lg border border-slate-200">
                @foreach ($toolsInCategory as $teacherTool)
                    <li class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <span><strong class="text-slate-900">{{ $teacherTool->tool_name }}</strong> — Nivel {{ $teacherTool->level }}</span>
                        <div class="card-actions-footer action-row">
                            <x-ui.dropdown label="Acciones" table>
                                <x-ui.dropdown-link :href="route('teachers.tools.edit', [$teacher, $teacherTool])">Editar</x-ui.dropdown-link>
                                <form method="POST" action="{{ route('teachers.tools.destroy', [$teacher, $teacherTool]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" role="menuitem" data-variant="danger">Eliminar</button>
                                </form>
                            </x-ui.dropdown>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    @endforeach

    <h3 class="pt-2 text-base font-semibold text-slate-900">Añadir programa o herramienta</h3>
    <form method="POST" action="{{ route('teachers.tools.store', $teacher) }}">
        @include('teachers.tools._form', [
            'teacherTool' => null,
            'submitLabel' => 'Añadir herramienta',
        ])
    </form>
    </div>
    </x-ui.card>

    <x-ui.card title="Documentos privados">
    <div class="section-stack">

    @if ($teacher->documents->isEmpty())
        <p class="empty-state">No hay documentos registrados.</p>
    @else
        <x-ui.table class="responsive-card-table">
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th>Archivo</th>
                    <th>Tamaño</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($teacher->documents as $document)
                    <tr>
                        <td data-label="Categoría">{{ match ($document->category) {
                            'cv' => 'Currículum',
                            'photo' => 'Fotografía',
                            'qualification' => 'Título o certificado',
                            'other' => 'Otro',
                        } }}</td>
                        <td class="responsive-break-anywhere" data-label="Archivo">{{ $document->original_name }}</td>
                        <td data-label="Tamaño">{{ $document->size_bytes === null ? '—' : number_format($document->size_bytes / 1024, 1, ',', '.').' KB' }}</td>
                        <td data-label="Fecha">{{ $document->created_at->format('d/m/Y H:i') }}</td>
                        <td class="responsive-card-actions" data-label="Acciones">
                            <x-ui.dropdown label="Acciones" table>
                                <x-ui.dropdown-link :href="route('teachers.documents.download', [$teacher, $document])">Descargar</x-ui.dropdown-link>
                                <form method="POST" action="{{ route('teachers.documents.destroy', [$teacher, $document]) }}"
                                    onsubmit="return confirm('¿Eliminar este documento privado?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" role="menuitem" data-variant="danger">Eliminar</button>
                                </form>
                            </x-ui.dropdown>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
    @endif

    <h3 class="pt-2 text-base font-semibold text-slate-900">Subir documento</h3>
    <form method="POST" action="{{ route('teachers.documents.store', $teacher) }}" enctype="multipart/form-data">
        @csrf
        <div class="form-grid">
        <div class="form-group">
            <label class="form-label" for="document_category">Categoría</label>
            <select class="form-control" id="document_category" name="category" required>
                <option value="cv" @selected(old('category') === 'cv')>Currículum</option>
                <option value="photo" @selected(old('category') === 'photo')>Fotografía</option>
                <option value="qualification" @selected(old('category') === 'qualification')>Título o certificado</option>
                <option value="other" @selected(old('category') === 'other')>Otro</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="document">Archivo</label>
            <input class="form-control" id="document" name="document" type="file"
                accept=".pdf,.docx,.jpg,.jpeg,.png,.webp" required>
        </div>
        <p class="text-sm text-slate-500 sm:col-span-2">Máximo 10 MB. El formato debe ser compatible con la categoría seleccionada.</p>
        <div class="form-actions"><x-ui.button type="submit">Subir documento</x-ui.button></div>
        </div>
    </form>
    </div>
    </x-ui.card>

    <x-ui.card title="Tarifas" description="Importes vigentes, programados e históricos del profesor.">
    <div class="section-stack">
        @include('teachers.prices._list', [
            'title' => 'Vigentes',
            'prices' => $currentPrices,
            'emptyMessage' => 'No hay tarifas vigentes.',
            'allowClose' => true,
        ])
        @include('teachers.prices._list', [
            'title' => 'Programadas',
            'prices' => $scheduledPrices,
            'emptyMessage' => 'No hay tarifas programadas.',
            'allowClose' => true,
        ])
        @include('teachers.prices._list', [
            'title' => 'Históricas',
            'prices' => $historicalPrices,
            'emptyMessage' => 'No hay tarifas históricas.',
            'allowClose' => false,
        ])

        @if ($activeServices->isEmpty())
            <x-ui.alert>
                No hay servicios activos. Activa o crea uno en
                <a class="font-semibold underline" href="{{ route('services.index') }}">Gestión de servicios</a>
                para poder añadir tarifas.
            </x-ui.alert>
        @else
            <h3 class="pt-2 text-base font-semibold text-slate-900">Añadir tarifa</h3>
            <form method="POST" action="{{ route('teachers.prices.store', $teacher) }}">
                @include('teachers.prices._form')
            </form>
        @endif
    </div>
    </x-ui.card>

    <x-ui.card title="Materias">
    <div class="section-stack">

    @if ($teacher->subjects->isEmpty())
        <p class="empty-state">No hay materias asociadas.</p>
    @else
        @foreach ($teacher->subjects as $subject)
            <section class="item-panel">
                <h3 class="font-semibold text-slate-900">{{ $subject->name }}</h3>
                <form method="POST" action="{{ route('teachers.subjects.update', [$teacher, $subject]) }}">
                    @csrf
                    @method('PATCH')
                    <div class="form-group mt-3">
                    <label class="form-label" for="details-{{ $subject->id }}">Detalles</label>
                    <textarea class="form-control" id="details-{{ $subject->id }}" name="details">{{ $subject->pivot->details }}</textarea>
                    </div>
                    <div class="action-row mt-3"><x-ui.button type="submit" variant="secondary">Actualizar detalles</x-ui.button></div>
                </form>
                <form method="POST" action="{{ route('teachers.subjects.destroy', [$teacher, $subject]) }}" class="mt-2 inline">
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" variant="danger">Desasociar materia</x-ui.button>
                </form>
            </section>
        @endforeach
    @endif

    <h3 class="pt-2 text-base font-semibold text-slate-900">Asociar o crear una materia</h3>
    <form method="POST" action="{{ route('teachers.subjects.store', $teacher) }}">
        @csrf
        <div class="form-grid">
        <div class="form-group">
            <label class="form-label" for="subject_id">Materia existente</label>
            <select class="form-control" id="subject_id" name="subject_id">
                <option value="">Selecciona una materia</option>
                @foreach ($availableSubjects as $subject)
                    <option value="{{ $subject->id }}" @selected((string) old('subject_id') === (string) $subject->id)>
                        {{ $subject->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <p class="self-end pb-3 text-sm text-slate-500">O crea una materia nueva:</p>
        <div class="form-group">
            <label class="form-label" for="subject_name">Nombre de la nueva materia</label>
            <input class="form-control" id="subject_name" name="subject_name" type="text" maxlength="150" value="{{ old('subject_name') }}">
        </div>
        <div class="form-group">
            <label class="form-label" for="subject_description">Descripción</label>
            <textarea class="form-control" id="subject_description" name="subject_description">{{ old('subject_description') }}</textarea>
        </div>
        <div class="form-group-wide">
            <label class="form-label" for="details">Detalles para este profesor</label>
            <textarea class="form-control" id="details" name="details">{{ old('details') }}</textarea>
        </div>
        <div class="form-actions"><x-ui.button type="submit">Asociar materia</x-ui.button></div>
        </div>
    </form>
    </div>
    </x-ui.card>

    <x-ui.card title="Disponibilidad">
    <div class="section-stack">

    @if ($teacher->availabilities->isEmpty())
        <p class="empty-state">No hay franjas de disponibilidad.</p>
    @else
        <x-ui.table class="responsive-card-table">
            <thead>
                <tr>
                    <th>Día</th>
                    <th>Horario</th>
                    <th>Modalidad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($teacher->availabilities as $availability)
                    <tr>
                        <td data-label="Día">{{ [1 => 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'][$availability->day_of_week] }}</td>
                        <td data-label="Horario">{{ substr($availability->start_time, 0, 5) }}–{{ substr($availability->end_time, 0, 5) }}</td>
                        <td data-label="Modalidad">{{ $availability->modality === 'online' ? 'En línea' : 'Presencial' }}</td>
                        <td class="responsive-card-actions" data-label="Acciones">
                            <x-ui.dropdown label="Acciones" table>
                                <x-ui.dropdown-link :href="route('teachers.availabilities.edit', [$teacher, $availability])">Editar</x-ui.dropdown-link>
                                <form method="POST" action="{{ route('teachers.availabilities.destroy', [$teacher, $availability]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" role="menuitem" data-variant="danger">Eliminar</button>
                                </form>
                            </x-ui.dropdown>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
    @endif

    <h3 class="pt-2 text-base font-semibold text-slate-900">Añadir disponibilidad</h3>
    <form method="POST" action="{{ route('teachers.availabilities.store', $teacher) }}">
        @include('teachers.availabilities._form', [
            'availability' => null,
            'submitLabel' => 'Añadir disponibilidad',
        ])
    </form>
    </div>
    </x-ui.card>
    </div>
@endsection
