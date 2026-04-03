@extends('layouts.app')

@section('content')
<h1>Liste des exécutions</h1>

<a href="{{ route('executions.create') }}">Nouvelle exécution</a>

@if(session('success'))
    <p>{{ session('success') }}</p>
@endif

<table border="1">
    <tr>
        <th>Numéro</th>
        <th>Jugement</th>
        <th>Statut</th>
        <th>Responsable</th>
        <th>Date notification</th>
        <th>Actions</th>
    </tr>

    @foreach($executions as $e)
        <tr>
            <td>{{ $e->numero_dossier_execution }}</td>
            <td>{{ $e->id_jugement }}</td>
            <td>{{ $e->statut->statut_execution ?? '' }}</td>
            <td>{{ $e->responsable->name ?? '' }}</td>
            <td>{{ $e->date_notification }}</td>
            <td>
                <a href="{{ route('executions.show', $e) }}">Voir</a>
                <a href="{{ route('executions.edit', $e) }}">Modifier</a>

                <form method="POST" action="{{ route('executions.destroy', $e) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Supprimer</button>
                </form>
            </td>
        </tr>
    @endforeach
</table>

{{ $executions->links() }}
@endsection