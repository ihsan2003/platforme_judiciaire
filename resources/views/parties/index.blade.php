@extends('layouts.app')

@section('title', 'Gestion des Parties')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Parties</h1>
        <a href="{{ route('parties.create') }}" class="btn btn-primary">
            + Nouvelle Partie
        </a>
    </div>

    {{-- Message flash de succès --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Formulaire de recherche --}}
    <form method="GET" action="{{ route('parties.index') }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control"
                   placeholder="Rechercher par nom, identifiant, email..."
                   value="{{ request('search') }}">
            <select name="type_personne" class="form-select">
                <option value="">Tous types</option>
                <option value="physique" @selected(request('type_personne') === 'physique')>Physique</option>
                <option value="morale"   @selected(request('type_personne') === 'morale')>Morale</option>
            </select>
            <button class="btn btn-outline-secondary" type="submit">Filtrer</button>
            <a href="{{ route('parties.index') }}" class="btn btn-outline-danger">Réinitialiser</a>
        </div>
    </form>

    {{-- Tableau --}}
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Nom</th>
                <th>Type</th>
                <th>Identifiant</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($parties as $partie)
            <tr>
                <td>{{ $partie->nom_partie }}</td>
                <td>{{ ucfirst($partie->type_personne) }}</td>
                <td>{{ $partie->identifiant_unique }}</td>
                <td>{{ $partie->telephone ?? '—' }}</td>
                <td>{{ $partie->email ?? '—' }}</td>
                <td>
                    <a href="{{ route('parties.show', $partie) }}" class="btn btn-sm btn-info">Voir</a>
                    <a href="{{ route('parties.edit', $partie) }}" class="btn btn-sm btn-warning">Éditer</a>
                    <form action="{{ route('parties.destroy', $partie) }}" method="POST"
                          style="display:inline"
                          onsubmit="return confirm('Supprimer cette partie ?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted">Aucune partie trouvée.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    {{ $parties->links() }}
</div>
@endsection