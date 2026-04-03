@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Liste des tribunaux</h1>

    <a href="{{ route('tribunaux.create') }}" class="btn btn-success mb-3">
        Ajouter
    </a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Type</th>
                <th>Province</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @foreach($tribunaux as $tribunal)
                <tr>
                    <td>{{ $tribunal->nom_tribunal }}</td>
                    <td>{{ $tribunal->typeTribunal->tribunal ?? '' }}</td>
                    <td>{{ $tribunal->province->province ?? '' }}</td>
                    <td>
                        <a href="{{ route('tribunaux.show', $tribunal) }}" class="btn btn-info">Voir</a>
                        <a href="{{ route('tribunaux.edit', $tribunal) }}" class="btn btn-warning">Modifier</a>

                        <form action="{{ route('tribunaux.destroy', $tribunal) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger" onclick="return confirm('Supprimer ?')">
                                Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $tribunaux->links() }}
</div>
@endsection