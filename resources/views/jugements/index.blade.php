@extends('layouts.app')

@section('content')
<h1>Liste des jugements</h1>

<a href="{{ route('jugements.create') }}">Nouveau jugement</a>

@if(session('success'))
    <p>{{ session('success') }}</p>
@endif

<table border="1">
    <tr>
        <th>Date</th>
        <th>Dossier</th>
        <th>Juge</th>
        <th>Définitif</th>
        <th>Actions</th>
    </tr>

    @foreach($jugements as $j)
        <tr>
            <td>{{ $j->date_jugement }}</td>
            <td>{{ $j->id_dossier_tribunal }}</td>
            <td>{{ $j->juge->nom_complet ?? '' }}</td>
            <td>{{ $j->est_definitif ? 'Oui' : 'Non' }}</td>
            <td>
                <a href="{{ route('jugements.show', $j) }}">Voir</a>
                <a href="{{ route('jugements.edit', $j) }}">Modifier</a>

                <form method="POST" action="{{ route('jugements.destroy', $j) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Supprimer</button>
                </form>
            </td>
        </tr>
    @endforeach
</table>

{{ $jugements->links() }}
@endsection