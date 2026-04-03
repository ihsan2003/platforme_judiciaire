@extends('layouts.app')

@section('content')
<h1>Créer un jugement</h1>

<form method="POST" action="{{ route('jugements.store') }}">
    @csrf

    <label>Dossier</label>
    <select name="id_dossier_tribunal">
        @foreach($dossierTribunaux as $dt)
            <option value="{{ $dt->id }}">
                Dossier #{{ $dt->dossier->numero_dossier_interne ?? $dt->id }}
                - {{ $dt->tribunal->nom ?? '' }}
            </option>
        @endforeach
    </select>

    <label>Juge</label>
    <select name="id_juge">
        @foreach($juges as $j)
            <option value="{{ $j->id }}">{{ $j->nom_complet }}</option>
        @endforeach
    </select>

    <label>Date jugement</label>
    <input type="date" name="date_jugement">

    <label>Contenu</label>
    <textarea name="contenu_dispositif"></textarea>

    <label>Définitif</label>
    <input type="checkbox" name="est_definitif" value="1">

    <label>Parties</label>
    <select name="parties[]" multiple>
        @foreach($parties as $p)
            <option value="{{ $p->id }}">{{ $p->nom_partie }}</option>
        @endforeach
    </select>

    <button type="submit">Enregistrer</button>
</form>
@endsection