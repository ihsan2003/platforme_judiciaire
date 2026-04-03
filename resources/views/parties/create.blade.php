@extends('layouts.app')
@section('title', 'Nouvelle Partie')

@section('content')
<div class="container" style="max-width: 700px">
    <h1>Nouvelle Partie</h1>

    <form action="{{ route('parties.store') }}" method="POST">
        @csrf
        @include('parties._form')  {{-- Formulaire partagé avec edit --}}
        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="{{ route('parties.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection