@extends('layouts.app')

@section('content')
<h1>Liste des audiences</h1>

<a href="{{ route('audiences.create') }}">Créer une audience</a>

@if(session('success'))
    <p>{{ session('success') }}</p>
@endif

<table border="1">
    <tr>
        <th>Date</th>
        <th>Juge</th>
        <th>Type</th>
        <th>Actions</th>
    </tr>

    @foreach($audiences as $audience)
        <tr>
            <td>{{ $audience->date_audience }}</td>
            <td>{{ $audience->juge->nom ?? '' }}</td>
            <td>{{ $audience->typeAudience->libelle ?? '' }}</td>
            <td>
                <a href="{{ route('audiences.show', $audience) }}">Voir</a>
                <a href="{{ route('audiences.edit', $audience) }}">Modifier</a>

                <form action="{{ route('audiences.destroy', $audience) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Supprimer</button>
                </form>
            </td>
        </tr>
    @endforeach
</table>

{{ $audiences->links() }}
@endsection