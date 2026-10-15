@extends('layouts.app')

@section('title', 'Editar servicio')

@section('content')
    <x-ui.page-header title="Editar servicio" description="Modifica su nombre visible o descripción sin alterar el código interno." />
    <x-ui.form-errors />
    <x-ui.card>
        <form method="POST" action="{{ route('services.update', $service) }}">
            @include('services._form', ['service' => $service, 'submitLabel' => 'Guardar cambios'])
        </form>
    </x-ui.card>
@endsection
