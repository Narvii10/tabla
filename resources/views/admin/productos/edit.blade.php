@extends('layouts.app')

@section('title', 'Editar producto')

@section('content')
    <x-page-header :title="$producto->nombre" subtitle="Editar producto" :back="route('admin.productos.index')" />

    {{-- enctype multipart para poder subir la foto --}}
    <form method="POST" action="{{ route('admin.productos.update', $producto) }}" enctype="multipart/form-data">
        @method('PUT')
        @include('admin.productos._form')
    </form>
@endsection
