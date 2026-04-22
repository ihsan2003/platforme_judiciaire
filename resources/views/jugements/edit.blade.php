@extends('layouts.app')

@section('content')
<h1>Créer un jugement</h1>

<form action="{{ route('jugements.update', $jugement) }}" method="POST">
    @csrf
    @method('PUT')

    <label>Dossier</label>
    <select name="id_dossier_tribunal">
        @foreach($dossiers as $d)
            <option value="{{ $d->id }}">{{ $d->id }}</option>
        @endforeach
    </select>

    <label>Juge</label>
    <select name="id_juge">
        @foreach($juges as $j)
            <option value="{{ $j->id }}">{{ $j->nom_complet }}</option>
        @endforeach
    </select>

    <label>Date jugement</label>
    <input type="date" name="date_jugement" value="{{ $jugement->date_jugement }}">

    <label>Contenu</label>
    <textarea name="contenu_dispositif" value="{{ $jugement->contenu_dispositif }}"></textarea>

    <label>Définitif</label>
    <input type="checkbox" name="est_definitif" value="1" {{ $jugement->est_definitif ? 'checked' : '' }}>

    <label>Parties</label>
    <select name="parties[]" multiple>
        @foreach($parties as $p)
            <option value="{{ $p->id }}"
            {{ $jugement->parties->contains($p->id) ? 'selected' : '' }}>
            {{ $p->nom_partie }}</option>
        @endforeach
    </select>

    <button type="submit">Enregistrer</button>
</form>
@endsection