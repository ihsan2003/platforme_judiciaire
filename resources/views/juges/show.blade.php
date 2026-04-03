@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Détail du juge</h1>

    <div class="card">
        <div class="card-body">
            <p><strong>Nom :</strong> {{ $juge->nom_complet }}</p>
            <p><strong>Grade :</strong> {{ $juge->grade }}</p>
            <p><strong>Spécialisation :</strong> {{ $juge->specialisation }}</p>
            <p><strong>Tribunal :</strong> {{ $juge->tribunal->nom_tribunal ?? '' }}</p>
        </div>
    </div>

    <a href="{{ route('juges.index') }}" class="btn btn-secondary mt-3">
        Retour
    </a>
</div>
@endsection