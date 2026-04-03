@extends('layouts.app')
@section('title', 'Modifier la Partie')

@section('content')
<div class="container" style="max-width: 700px">
    <h1>Modifier : {{ $partie->nom_partie }}</h1>

    <form action="{{ route('parties.update', $partie) }}" method="POST">
        @csrf
        @method('PUT')
        @include('parties._form')
        <button type="submit" class="btn btn-warning">Mettre à jour</button>
        <a href="{{ route('parties.show', $partie) }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection