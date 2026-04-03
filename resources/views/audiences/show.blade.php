@extends('layouts.app')

@section('content')
<h1>Détail audience</h1>

<p>Date: {{ $audience->date_audience }}</p>
<p>Juge: {{ $audience->juge->nom_complet }}</p>
<p>Type: {{ $audience->typeAudience->type_audience }}</p>
<p>Résultat: {{ $audience->resultat_audience }}</p>

<a href="{{ route('audiences.index') }}">Retour</a>
@endsection