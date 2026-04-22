@extends('layouts.app')

@section('content')
<h1>Détails Finance</h1>

<p><b>Jugement:</b> {{ $finance->jugement->id }}</p>
<p><b>Montant condamné:</b> {{ $finance->montant_condamne }}</p>
<p><b>Montant payé:</b> {{ $finance->montant_paye }}</p>
<p><b>Restant:</b> {{ $finance->montant_restant }}</p>

<p>
    <b>Statut:</b>
    {{ $finance->est_solde ? 'Soldé' : 'Non soldé' }}
</p>

<a href="{{ route('finances.index') }}">Retour</a>
@endsection