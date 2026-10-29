@extends('layouts.app')

@section('title', 'Nuevo producto')

@section('content')
    <x-page-header title="Nuevo producto" subtitle="Agrega un plato, vino o bebida al menú." :back="route('admin.productos.index')" />

    {{-- enctype multipart para poder subir la foto --}}
    <form method="POST" action="{{ route('admin.productos.store') }}" enctype="multipart/form-data">
        @include('admin.productos._form')
    </form>
@endsection
