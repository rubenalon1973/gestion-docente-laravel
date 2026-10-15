@extends('layouts.app')

@section('title', 'Añadir profesor')

@section('content')
    <x-ui.page-header title="Añadir profesor" description="Registra los datos básicos del profesor.">
        <x-slot:actions>
            <x-ui.button href="{{ route('teachers.index') }}" variant="secondary">Volver al listado</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.form-errors />

    <x-ui.card>
        <form method="POST" action="{{ route('teachers.store') }}">
            @include('teachers._form', [
                'teacher' => null,
                'submitLabel' => 'Guardar profesor',
            ])
        </form>
    </x-ui.card>
@endsection
