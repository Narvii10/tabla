@extends('layouts.app')

@section('title', 'Nueva categoría')

@section('content')
    <x-page-header title="Nueva categoría" subtitle="Una sección nueva para el menú." :back="route('admin.categorias.index')" />

    <form method="POST" action="{{ route('admin.categorias.store') }}">
        @include('admin.categorias._form')
    </form>
@endsection
