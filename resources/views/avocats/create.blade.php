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
    <input type="tel"
        name="telephone"
        class="form-control @error('telephone') is-invalid @enderror"
        placeholder="Ex : 0612345678"
        pattern="^(\+212|00212|0)(5|6|7)[0-9]{8}$"
        title="Format attendu : 0612345678 ou +212612345678"
        value="{{ old('telephone', $avocat->telephone ?? '') }}">
    <input type="email" name="email" placeholder="Email" value="{{ old('email') }}">
    <button type="submit">Créer</button>
</form>
<a href="{{ route('avocats.index') }}">Retour à la liste</a>