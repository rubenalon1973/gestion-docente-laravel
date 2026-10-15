@extends('layouts.app')

@section('title', 'Editar profesor')

@section('content')
    <x-ui.page-header title="Editar profesor" description="Actualiza los datos de {{ $teacher->first_name }} {{ $teacher->last_names }}.">
        <x-slot:actions>
            <x-ui.button href="{{ route('teachers.show', $teacher) }}" variant="secondary">Volver a la ficha</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.form-errors />

    <x-ui.card>
        <form method="POST" action="{{ route('teachers.update', $teacher) }}">
            @method('PUT')
            @include('teachers._form', [
                'teacher' => $teacher,
                'submitLabel' => 'Guardar cambios',
            ])
        </form>
    </x-ui.card>
@endsection
