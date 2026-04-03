@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Liste des juges</h1>

    <a href="{{ route('juges.create') }}" class="btn btn-success mb-3">
        Ajouter
    </a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Grade</th>
                <th>Spécialisation</th>
                <th>Tribunal</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @foreach($juges as $juge)
                <tr>
                    <td>{{ $juge->nom_complet }}</td>
                    <td>{{ $juge->grade }}</td>
                    <td>{{ $juge->specialisation }}</td>
                    <td>{{ $juge->tribunal->nom_tribunal ?? '' }}</td>
                    <td>
                        <a href="{{ route('juges.show', $juge) }}" class="btn btn-info">Voir</a>
                        <a href="{{ route('juges.edit', $juge) }}" class="btn btn-warning">Modifier</a>

                        <form action="{{ route('juges.destroy', $juge) }}" method="POST" style="display:inline;">
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

    {{ $juges->links() }}
</div>
@endsection