{{-- resources/views/parties/_form.blade.php --}}
{{-- Utilisé à la fois par create et edit --}}

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-3">
    <label class="form-label">Nom de la partie *</label>
    <input type="text" name="nom_partie" class="form-control @error('nom_partie') is-invalid @enderror"
           value="{{ old('nom_partie', $partie->nom_partie ?? '') }}">
    @error('nom_partie') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label class="form-label">Type de personne *</label>
    <select name="type_personne" class="form-select @error('type_personne') is-invalid @enderror">
        <option value="">-- Choisir --</option>
        <option value="physique" @selected(old('type_personne', $partie->type_personne ?? '') === 'physique')>Physique</option>
        <option value="morale"   @selected(old('type_personne', $partie->type_personne ?? '') === 'morale')>Morale</option>
    </select>
    @error('type_personne') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label class="form-label">Identifiant unique *</label>
    <input type="text" name="identifiant_unique" class="form-control @error('identifiant_unique') is-invalid @enderror"
           value="{{ old('identifiant_unique', $partie->identifiant_unique ?? '') }}">
    @error('identifiant_unique') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="row">
    <div class="col mb-3">
        <label class="form-label">Téléphone</label>
        <input type="text" name="telephone" class="form-control"
               value="{{ old('telephone', $partie->telephone ?? '') }}">
    </div>
    <div class="col mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $partie->email ?? '') }}">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Adresse</label>
    <textarea name="adresse" class="form-control" rows="2">{{ old('adresse', $partie->adresse ?? '') }}</textarea>
</div>