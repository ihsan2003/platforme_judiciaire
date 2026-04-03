<h1>Détails de l'avocat</h1>

<p>Nom : {{ $avocat->nom_avocat }}</p>
<p>Téléphone : {{ $avocat->telephone }}</p>
<p>Email : {{ $avocat->email }}</p>

<h2>Dossiers associés</h2>
<ul>
    @foreach($avocat->dossierParties as $dossier)
        <li>{{ $dossier->nom_dossier ?? 'Nom du dossier' }}</li>
    @endforeach
</ul>

<a href="{{ route('avocats.index') }}">Retour à la liste</a>