@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Détail du tribunal</h1>

    <div class="card">
        <div class="card-body">
            <p><strong>Nom :</strong> {{ $tribunal->nom_tribunal }}</p>
            <p><strong>Type :</strong> {{ $tribunal->typeTribunal->tribunal ?? '' }}</p>
            <p><strong>Province :</strong> {{ $tribunal->province->province ?? '' }}</p>
        </div>
    </div>

    <a href="{{ route('tribunaux.index') }}" class="btn btn-secondary mt-3">
        Retour
    </a>
</div>
@endsection

