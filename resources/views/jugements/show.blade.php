@extends('layouts.app')

@section('content')
<h1>Détail jugement</h1>

<p>Date : {{ $jugement->date_jugement }}</p>
<p>Juge : {{ $jugement->juge->nom_complet }}</p>
<p>Contenu : {{ $jugement->contenu_dispositif }}</p>
<p>Définitif : {{ $jugement->est_definitif ? 'Oui' : 'Non' }}</p>

<h3>Parties :</h3>
<ul>
    @foreach($jugement->parties as $p)
        <li>{{ $p->nom_partie }}</li>
    @endforeach
</ul>

<p>Délai recours restant : {{ $jugement->delai_recours_restant }}</p>

<a href="{{ route('jugements.index') }}">Retour</a>
@endsection