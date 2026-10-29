@extends('layouts.app')

@section('title', 'Nueva mesa')

@section('content')
    <x-page-header title="Nueva mesa" subtitle="Al guardarla se genera su código QR." :back="route('admin.mesas.index')" />

    <div class="row g-4">
        <div class="col-lg-7">
            <form method="POST" action="{{ route('admin.mesas.store') }}">
                @include('admin.mesas._form')
            </form>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-body p-4">
                    <h2 class="h5 mb-2">Código QR</h2>
                    <p class="small text-muted mb-0">Se genera solo al guardar la mesa. Después podrás imprimirlo.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
