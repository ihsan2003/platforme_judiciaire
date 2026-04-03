@extends('layouts.app')

@section('content')
<h1>Détail exécution</h1>

<p>Numéro : {{ $execution->numero_dossier_execution }}</p>
<p>Jugement : {{ $execution->id_jugement }}</p>
<p>Statut : {{ $execution->statut->statut_execution ?? '' }}</p>
<p>Responsable : {{ $execution->responsable->name ?? '' }}</p>
<p>Date notification : {{ $execution->date_notification }}</p>
<p>Date exécution : {{ $execution->date_execution }}</p>

@if($execution->finance)
    <h3>Finance liée :</h3>
    <p>Montant : {{ $execution->finance->montant ?? '' }}</p>
@endif

<a href="{{ route('executions.index') }}">Retour</a>
@endsection