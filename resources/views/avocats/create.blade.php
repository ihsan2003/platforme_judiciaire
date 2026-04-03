<h1>Créer un avocat</h1>

@if ($errors->any())
    <ul>
        @foreach ($errors->all() as $error)
            <li style="color:red">{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form action="{{ route('avocats.store') }}" method="POST">
    @csrf
    <input type="text" name="nom_avocat" placeholder="Nom de l'avocat" value="{{ old('nom_avocat') }}">
    <input type="text" name="telephone" placeholder="Téléphone" value="{{ old('telephone') }}">
    <input type="email" name="email" placeholder="Email" value="{{ old('email') }}">
    <button type="submit">Créer</button>
</form>
<a href="{{ route('avocats.index') }}">Retour à la liste</a>