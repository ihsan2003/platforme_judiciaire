<div class="mb-3">
    <label>Nom complet</label>
    <input type="text" name="nom_complet" class="form-control"
           value="{{ old('nom_complet', $juge->nom_complet ?? '') }}">
</div>

<div class="mb-3">
    <label>Grade</label>
    <input type="text" name="grade" class="form-control"
           value="{{ old('grade', $juge->grade ?? '') }}">
</div>

<div class="mb-3">
    <label>Spécialisation</label>
    <input type="text" name="specialisation" class="form-control"
           value="{{ old('specialisation', $juge->specialisation ?? '') }}">
</div>

<div class="mb-3">
    <label>Tribunal</label>
    <select name="id_tribunal" class="form-control">
        @foreach($tribunaux as $tribunal)
            <option value="{{ $tribunal->id }}"
                {{ old('id_tribunal', $juge->id_tribunal ?? '') == $tribunal->id ? 'selected' : '' }}>
                {{ $tribunal->nom_tribunal }}
            </option>
        @endforeach
    </select>
</div>

<button type="submit" class="btn btn-primary">Enregistrer</button>