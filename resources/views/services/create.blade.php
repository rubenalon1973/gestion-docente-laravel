@extends('layouts.app')

@section('title', 'Añadir servicio')

@section('content')
    <x-ui.page-header title="Añadir servicio" description="El código interno se generará automáticamente y permanecerá estable." />
    <x-ui.form-errors />
    <x-ui.card>
        <form method="POST" action="{{ route('services.store') }}">
            @include('services._form', ['service' => null, 'submitLabel' => 'Guardar servicio'])
        </form>
    </x-ui.card>
@endsection
