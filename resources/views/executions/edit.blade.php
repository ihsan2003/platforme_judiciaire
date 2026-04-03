@extends('layouts.app')

@section('content')
<h1>Créer une audience</h1>

<form method="POST" action="{{ route('audiences.store') }}">
    @csrf

    <label>Dossier</label>
    <select name="id_dossier_tribunal">
        @foreach($dossiers as $d)
            <option value="{{ $d->id }}">{{ $d->id }}</option>
        @endforeach
    </select>

    <label>Type audience</label>
    <select name="id_type_audience">
        @foreach($types as $t)
            <option value="{{ $t->id }}">{{ $t->type_audience }}</option>
        @endforeach
    </select>

    <label>Juge</label>
    <select name="id_juge">
        @foreach($juges as $j)
            <option value="{{ $j->id }}">{{ $j->nom_complet }}</option>
        @endforeach
    </select>

    <label>Date audience</label>
    <input type="date" name="date_audience" value="{{ $audience->date_audience }}">
    
    <label>Prochaine date</label>
    <input type="date" name="date_prochaine_audience" value="{{ $audience->date_prochaine_audience }}">

    <label>Présence demandeur</label>
    <input type="checkbox" name="presence_demandeur" value="{{ $audience->presence_demandeur}}">

    <label>Présence défendeur</label>
    <input type="checkbox" name="presence_defendeur" value="{{ $audience->presence_defendeur}}">

    <label>Résultat</label>
    <textarea name="resultat_audience" value="{{ $audience->resultat_audience }}"></textarea>

    <label>Actions demandées</label>
    <textarea name="actions_demandees" value="{{ $audience->actions_demandees }}"></textarea>

    <button type="submit">Enregistrer</button>
</form>
@endsection