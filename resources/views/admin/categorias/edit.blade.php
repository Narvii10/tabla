@extends('layouts.app')

@section('title', 'Editar categoría')

@section('content')
    <x-page-header :title="$categoria->nombre" subtitle="Editar categoría" :back="route('admin.categorias.index')" />

    <form method="POST" action="{{ route('admin.categorias.update', $categoria) }}">
        @method('PUT')
        @include('admin.categorias._form')
    </form>
@endsection
