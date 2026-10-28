@extends('layouts.public')

@section('title', 'Menú')

@isset($mesa)
    @section('subtitle', 'Mesa '.$mesa->numero)
@endisset

@section('content')
    <x-placeholder module="Menú" branch="feature/menu" />
@endsection
