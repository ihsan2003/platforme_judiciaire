@extends('layouts.app')

@section('content')
<h1>Créer une finance</h1>

<form method="POST" action="{{ route('finances.store') }}">
    @csrf

    <label>Jugement</label>
    <select name="id_jugement">
        @foreach($jugements as $j)
            <option value="{{ $j->id }}">
                Dossier #{{ $j->dossierTribunal->id ?? $j->id }}
            </option>
        @endforeach
    </select>

    <label>Montant réclamé demandeur</label>
    <input type="number" step="0.01" name="montant_reclame_demandeur">

    <label>Montant réclamé défendeur</label>
    <input type="number" step="0.01" name="montant_reclame_defendeur">

    <label>Montant condamné</label>
    <input type="number" step="0.01" name="montant_condamne">

    <label>Montant payé</label>
    <input type="number" step="0.01" name="montant_paye">

    <label>Date paiement</label>
    <input type="date" name="date_paiement">

    <label>Statut paiement</label>
    <input type="text" name="statut_paiement">

    <button type="submit">Enregistrer</button>
</form>
@endsection