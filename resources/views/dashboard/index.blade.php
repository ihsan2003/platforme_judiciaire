@extends('layouts.app')

@section('content')
<h1>Dashboard</h1>

<div class="grid">

    <h2>Dossiers</h2>
    <p>Total: {{ $totalDossiers }}</p>
    <p>En cours: {{ $dossiersEnCours }}</p>
    <p>Jugés: {{ $dossiersJuges }}</p>
    <p>Exécutés: {{ $dossiersExecutes }}</p>

    <h2>Réclamations</h2>
    <p>Total: {{ $totalReclamations }}</p>
    <p>Reçues: {{ $reclamationsRecues }}</p>
    <p>En cours: {{ $reclamationsEnCours }}</p>
    <p>Clôturées: {{ $reclamationsCloturees }}</p>

    <h2>Alertes</h2>
    <p>Audiences proches: {{ $audiencesProches }}</p>
    <p>Jugements non définitifs: {{ $jugementsNonDefinitifs }}</p>
    <p>Réclamations en attente: {{ $reclamationsEnAttente }}</p>

</div>
@endsection