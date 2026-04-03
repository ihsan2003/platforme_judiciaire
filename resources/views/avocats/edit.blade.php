<h1>Modifier l'avocat</h1>

@if ($errors->any())
    <ul>
        @foreach ($errors->all() as $error)
            <li style="color:red">{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form action="{{ route('avocats.update', $avocat->id) }}" method="POST">
    @csrf
    @method('PUT')
    <input type="text" name="nom_avocat" value="{{ old('nom_avocat', $avocat->nom_avocat) }}">
    <input type="text" name="telephone" value="{{ old('telephone', $avocat->telephone) }}">
    <input type="email" name="email" value="{{ old('email', $avocat->email) }}">
    <button type="submit">Modifier</button>
</form>
<a href="{{ route('avocats.index') }}">Retour à la liste</a>