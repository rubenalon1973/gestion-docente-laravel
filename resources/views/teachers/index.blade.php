@extends('layouts.app')

@section('title', 'Profesores')

@section('content')
    @php($exportFilters = collect($filters)->reject(fn ($value) => $value === null || $value === '')->all())

    <x-ui.page-header title="Profesores" description="Consulta y administra los datos registrados del profesorado.">
        <x-slot:actions>
            <x-ui.dropdown label="Exportar resultados">
                @if ($hasFilters)
                    <p>Se exportarán {{ $teachers->total() }} {{ $teachers->total() === 1 ? 'resultado' : 'resultados' }}.</p>
                @endif
                <x-ui.dropdown-link :href="route('teachers.export.pdf', $exportFilters)">PDF</x-ui.dropdown-link>
                <x-ui.dropdown-link :href="route('teachers.export.csv', $exportFilters)">CSV</x-ui.dropdown-link>
            </x-ui.dropdown>
            <x-ui.button href="{{ route('teachers.create') }}">Añadir profesor</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card title="Buscar y filtrar" description="Combina los criterios para localizar profesores.">
        <form class="form-grid" method="GET" action="{{ route('teachers.index') }}">
            <div class="form-group-wide">
                <label class="form-label" for="q">Búsqueda</label>
                <input class="form-control" id="q" name="q" type="search"
                    value="{{ $filters['q'] ?? '' }}"
                    placeholder="Nombre, DNI/NIE, contacto, ubicación, materia u observaciones">
            </div>

            <div class="form-group">
                <label class="form-label" for="status">Estado</label>
                <select class="form-control" id="status" name="status">
                    <option value="">Todos</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Activo</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactivo</option>
                    <option value="archived" @selected(($filters['status'] ?? '') === 'archived')>Archivado</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="subject_id">Materia</label>
                <select class="form-control" id="subject_id" name="subject_id">
                    <option value="">Todas</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected((string) ($filters['subject_id'] ?? '') === (string) $subject->id)>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="modality">Modalidad disponible</label>
                <select class="form-control" id="modality" name="modality">
                    <option value="">Todas</option>
                    <option value="in_person" @selected(($filters['modality'] ?? '') === 'in_person')>Presencial</option>
                    <option value="online" @selected(($filters['modality'] ?? '') === 'online')>En línea</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="creates_content">Creación de contenidos</label>
                <select class="form-control" id="creates_content" name="creates_content">
                    <option value="">Todos</option>
                    <option value="1" @selected(($filters['creates_content'] ?? '') === '1')>Sí</option>
                    <option value="0" @selected(($filters['creates_content'] ?? '') === '0')>No</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="updates_content">Actualización de contenidos</label>
                <select class="form-control" id="updates_content" name="updates_content">
                    <option value="">Todos</option>
                    <option value="1" @selected(($filters['updates_content'] ?? '') === '1')>Sí</option>
                    <option value="0" @selected(($filters['updates_content'] ?? '') === '0')>No</option>
                </select>
            </div>

            <div class="form-actions">
                <x-ui.button type="submit">Aplicar filtros</x-ui.button>
                @if ($hasFilters)
                    <x-ui.button href="{{ route('teachers.index') }}" variant="secondary">Limpiar filtros</x-ui.button>
                @endif
            </div>
        </form>
    </x-ui.card>

    <div class="mt-6">
        @if ($teachers->isEmpty())
            <x-ui.card>
                <p class="empty-state">
                    {{ $hasFilters ? 'No se encontraron profesores con los criterios indicados.' : 'No hay profesores registrados.' }}
                </p>
            </x-ui.card>
        @else
            <div class="mobile-card-list" aria-label="Profesores">
                @foreach ($teachers as $teacher)
                    <article class="mobile-record-card">
                        <div class="mobile-record-card-header">
                            <h2 class="mobile-record-card-title">{{ $teacher->first_name }} {{ $teacher->last_names }}</h2>
                            <x-ui.status-badge :status="$teacher->status" />
                        </div>
                        <div class="mobile-record-card-body">
                            <p><span class="mobile-record-card-label">Correo:</span>{{ $teacher->email ?: '—' }}</p>
                        </div>
                        <div class="mobile-record-card-actions">
                            <x-ui.dropdown label="Acciones" table>
                                <x-ui.dropdown-link :href="route('teachers.show', $teacher)">Consultar</x-ui.dropdown-link>
                                <x-ui.dropdown-link :href="route('teachers.edit', $teacher)">Editar</x-ui.dropdown-link>
                                <x-ui.dropdown-link :href="route('teachers.export-one.pdf', $teacher)">Exportar PDF</x-ui.dropdown-link>
                                <x-ui.dropdown-link :href="route('teachers.export-one.csv', $teacher)">Exportar CSV</x-ui.dropdown-link>
                            </x-ui.dropdown>
                        </div>
                    </article>
                @endforeach
            </div>

            <x-ui.card class="hidden md:block">
                <x-ui.table>
                <thead>
                    <tr>
                        <th>Apellidos</th>
                        <th>Nombre</th>
                        <th>Correo electrónico</th>
                        <th>Estado</th>
                        <th class="table-sticky-actions">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($teachers as $teacher)
                        <tr>
                            <td class="font-medium text-slate-900">{{ $teacher->last_names }}</td>
                            <td>{{ $teacher->first_name }}</td>
                            <td>{{ $teacher->email ?: '—' }}</td>
                            <td><x-ui.status-badge :status="$teacher->status" /></td>
                            <td class="table-sticky-actions">
                                <div class="table-actions">
                                    <x-ui.dropdown label="Acciones" table>
                                        <x-ui.dropdown-link :href="route('teachers.show', $teacher)">Consultar</x-ui.dropdown-link>
                                        <x-ui.dropdown-link :href="route('teachers.edit', $teacher)">Editar</x-ui.dropdown-link>
                                        <x-ui.dropdown-link :href="route('teachers.export-one.pdf', $teacher)">Exportar PDF</x-ui.dropdown-link>
                                        <x-ui.dropdown-link :href="route('teachers.export-one.csv', $teacher)">Exportar CSV</x-ui.dropdown-link>
                                    </x-ui.dropdown>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                </x-ui.table>
            </x-ui.card>

            <div class="mt-6">{{ $teachers->links() }}</div>
        @endif
    </div>
@endsection
