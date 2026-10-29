@extends('layouts.app')

@section('title', 'Nuevo usuario')

@section('content')
    <x-page-header title="Nuevo usuario" subtitle="Podrá entrar con su correo y contraseña." :back="route('admin.usuarios.index')" />

    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('admin.usuarios.store') }}">
                @include('admin.usuarios._form')
            </form>
        </div>
    </div>
@endsection
