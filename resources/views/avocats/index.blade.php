<h1>Liste des avocats</h1>

@if(session('success'))
    <div style="color: green">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div style="color: red">{{ session('error') }}</div>
@endif

<a href="{{ route('avocats.create') }}">Créer un nouvel avocat</a>

<ul>
    @foreach($avocats as $avocat)
        <li>
            {{ $avocat->nom_avocat }} - {{ $avocat->email }}
            <a href="{{ route('avocats.show', $avocat->id) }}">Voir</a>
            <a href="{{ route('avocats.edit', $avocat->id) }}">Modifier</a>
            <form action="{{ route('avocats.destroy', $avocat->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit">Supprimer</button>
            </form>
        </li>
    @endforeach
</ul>

{{ $avocats->links() }} <!-- Pagination -->