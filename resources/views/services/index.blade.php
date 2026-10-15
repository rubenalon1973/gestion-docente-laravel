@extends('layouts.app')

@section('title', 'Servicios')

@section('content')
    <x-ui.page-header title="Servicios" description="Administra los servicios disponibles para las tarifas del profesorado.">
        <x-slot:actions>
            <x-ui.button href="{{ route('services.create') }}">Añadir servicio</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($services->isEmpty())
        <x-ui.card>
            <p class="empty-state">No hay servicios registrados. Añade el primero para poder crear tarifas.</p>
        </x-ui.card>
    @else
        <div class="mobile-card-list" aria-label="Servicios">
            @foreach ($services as $service)
                <article class="mobile-record-card">
                    <div class="mobile-record-card-header">
                        <h2 class="mobile-record-card-title">{{ $service->name }}</h2>
                        <x-ui.status-badge :status="$service->is_active ? 'active' : 'inactive'" />
                    </div>
                    <div class="mobile-record-card-body">
                        <p>{{ $service->description ?: 'Sin descripción.' }}</p>
                        <p><span class="mobile-record-card-label">Código interno:</span><span class="break-all font-mono text-xs">{{ $service->code }}</span></p>
                    </div>
                    <div class="mobile-record-card-actions">
                        <x-ui.button href="{{ route('services.edit', $service) }}" variant="secondary">Editar</x-ui.button>
                        <form method="POST" action="{{ route('services.update-status', $service) }}">
                            @csrf
                            @method('PATCH')
                            <x-ui.button type="submit" :variant="$service->is_active ? 'danger' : 'primary'">
                                {{ $service->is_active ? 'Desactivar' : 'Activar' }}
                            </x-ui.button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>

        <x-ui.card class="hidden md:block">
            <x-ui.table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Código interno</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($services as $service)
                        <tr>
                            <td class="font-medium text-slate-900">{{ $service->name }}</td>
                            <td class="max-w-72 break-words">{{ $service->description ?: '—' }}</td>
                            <td class="max-w-64 break-all font-mono text-xs">{{ $service->code }}</td>
                            <td><x-ui.status-badge :status="$service->is_active ? 'active' : 'inactive'" /></td>
                            <td>
                                <div class="table-actions">
                                    <x-ui.button href="{{ route('services.edit', $service) }}" variant="secondary">Editar</x-ui.button>
                                    <form method="POST" action="{{ route('services.update-status', $service) }}">
                                        @csrf
                                        @method('PATCH')
                                        <x-ui.button type="submit" :variant="$service->is_active ? 'danger' : 'primary'">
                                            {{ $service->is_active ? 'Desactivar' : 'Activar' }}
                                        </x-ui.button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>
        </x-ui.card>
    @endif
@endsection
