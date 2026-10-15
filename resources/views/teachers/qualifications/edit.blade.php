@extends('layouts.app')

@section('title', 'Editar formación académica')

@section('content')
    <x-ui.page-header title="Editar formación académica" description="{{ $teacher->first_name }} {{ $teacher->last_names }}">
        <x-slot:actions>
            <x-ui.button href="{{ route('teachers.show', $teacher) }}" variant="secondary">Volver a la ficha</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.form-errors />

    <x-ui.card>
        <form method="POST" action="{{ route('teachers.qualifications.update', [$teacher, $qualification]) }}">
            @method('PUT')
            @include('teachers.qualifications._form', [
                'qualification' => $qualification,
                'submitLabel' => 'Guardar formación',
            ])
        </form>
    </x-ui.card>
@endsection
