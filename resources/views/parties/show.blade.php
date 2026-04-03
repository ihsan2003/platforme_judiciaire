@extends('layouts.app')

@section('title', $partie->nom_partie)

@section('content')
<div class="container">
    {{-- Titre et actions --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ $partie->nom_partie }}</h1>

    </div>

    {{-- Informations générales --}}
    <div class="card mb-4">
        <div class="card-header">Informations</div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Type</dt>
                <dd class="col-sm-9">{{ ucfirst($partie->type_personne) }}</dd>

                <dt class="col-sm-3">Identifiant</dt>
                <dd class="col-sm-9">{{ $partie->identifiant_unique }}</dd>

                <dt class="col-sm-3">Téléphone</dt>
                <dd class="col-sm-9">{{ $partie->telephone ?? '—' }}</dd>

                <dt class="col-sm-3">Email</dt>
                <dd class="col-sm-9">{{ $partie->email ?? '—' }}</dd>

                <dt class="col-sm-3">Adresse</dt>
                <dd class="col-sm-9">{{ $partie->adresse ?? '—' }}</dd>
            </dl>
        </div>
    </div>

    {{-- Dossiers liés --}}
    <div class="card mb-4">
        <div class="card-header">Dossiers judiciaires ({{ $partie->dossiers->count() }})</div>
        <div class="card-body">
            @forelse($partie->dossiers as $dossier)
                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <div>
                        <strong>{{ $dossier->numero_dossier_interne }}</strong>
                        <span class="badge bg-secondary">{{ $dossier->typeAffaire->type_affaire ?? '—' }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-{{ $dossier->est_actif ? 'success' : 'danger' }}">
                            {{ $dossier->statutDossier->statut_dossier ?? '—' }}
                        </span>
                        <a href="{{ route('dossiers.show', $dossier) }}" class="btn btn-sm btn-link">Voir</a>
                    </div>
                </div>
            @empty
                <p class="text-muted">Aucun dossier associé.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection