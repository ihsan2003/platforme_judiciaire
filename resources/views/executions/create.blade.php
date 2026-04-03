@extends('layouts.app')

@section('content')
<h1>Créer une exécution</h1>

<form method="POST" action="{{ route('executions.store') }}">
    @csrf

    <label>Jugement</label>
    <select name="id_jugement">
        @foreach($jugements as $j)
            <option value="{{ $j->id }}">{{ $j->id }}</option>
        @endforeach
    </select>

    <label>Numéro dossier</label>
    <input type="text" name="numero_dossier_execution">

    <label>Date notification</label>
    <input type="date" name="date_notification">

    <label>Statut</label>
    <select name="statut_execution">
        @foreach($statuts as $s)
            <option value="{{ $s->id }}">{{ $s->statut_execution }}</option>
        @endforeach
    </select>

    <label>Date exécution</label>
    <input type="date" name="date_execution">

    <label>Responsable</label>
    <select name="responsable_id">
        @foreach($responsables as $r)
            <option value="{{ $r->id }}">{{ $r->name }}</option>
        @endforeach
    </select>

    <button type="submit">Enregistrer</button>
</form>
@endsection