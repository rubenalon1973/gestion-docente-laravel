@extends('layouts.app')

@section('title', 'Editar programa o herramienta')

@section('content')
    <x-ui.page-header title="Editar programa o herramienta" description="{{ $teacher->first_name }} {{ $teacher->last_names }}">
        <x-slot:actions>
            <x-ui.button href="{{ route('teachers.show', $teacher) }}" variant="secondary">Volver a la ficha</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.form-errors />

    <x-ui.card>
        <form method="POST" action="{{ route('teachers.tools.update', [$teacher, $teacherTool]) }}">
            @method('PUT')
            @include('teachers.tools._form', [
                'teacherTool' => $teacherTool,
                'submitLabel' => 'Guardar herramienta',
            ])
        </form>
    </x-ui.card>
@endsection
