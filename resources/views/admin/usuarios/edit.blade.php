@extends('layouts.app')

@section('title', 'Editar usuario')

@section('content')
    <x-page-header :title="$usuario->name" subtitle="Editar usuario" :back="route('admin.usuarios.index')" />

    <div class="row">
        <div class="col-lg-8">
            @unless ($usuario->activo)
                <div class="alert alert-danger small">
                    <i class="bi bi-slash-circle me-1"></i>Este usuario está desactivado: no puede entrar al sistema.
                    Actívalo desde el listado.
                </div>
            @endunless

            <form method="POST" action="{{ route('admin.usuarios.update', $usuario) }}">
                @method('PUT')
                @include('admin.usuarios._form')
            </form>
        </div>
    </div>
@endsection
