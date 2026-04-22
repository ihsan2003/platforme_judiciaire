@extends('layouts.app')

@section('content')
<h1>Gestion des finances</h1>

<a href="{{ route('finances.create') }}">+ Ajouter finance</a>

<table border="1" cellpadding="10">
    <thead>
        <tr>
            <th>ID</th>
            <th>Dossier</th>
            <th>Montant condamné</th>
            <th>Payé</th>
            <th>Restant</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
        @foreach($finances as $finance)
            <tr>
                <td>{{ $finance->id }}</td>

                <td>
                    {{ $finance->jugement->dossierTribunal->id ?? '-' }}
                </td>

                <td>{{ $finance->montant_condamne }}</td>
                <td>{{ $finance->montant_paye }}</td>

                <td>
                    {{ $finance->montant_restant }}
                </td>

                <td>
                    @if($finance->est_solde)
                        ✅ Soldé
                    @else
                        ❌ Non soldé
                    @endif
                </td>

                <td>
                    <a href="{{ route('finances.show', $finance) }}">Voir</a>
                    <a href="{{ route('finances.edit', $finance) }}">Modifier</a>

                    <form method="POST" action="{{ route('finances.destroy', $finance) }}" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button onclick="return confirm('Supprimer ?')">X</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection