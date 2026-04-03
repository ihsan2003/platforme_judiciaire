<div class="mb-3">
    <label>Nom du tribunal</label>
    <input type="text" name="nom_tribunal" class="form-control"
           value="{{ old('nom_tribunal', $tribunal->nom_tribunal ?? '') }}">
</div>

<div class="mb-3">
    <label>Type de tribunal</label>
    <select name="id_type_tribunal" class="form-control">
        @foreach($types as $type)
            <option value="{{ $type->id }}"
                {{ old('id_type_tribunal', $tribunal->id_type_tribunal ?? '') == $type->id ? 'selected' : '' }}>
                {{ $type->tribunal }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label>Province</label>
    <select name="id_province" class="form-control">
        @foreach($provinces as $province)
            <option value="{{ $province->id }}"
                {{ old('id_province', $tribunal->id_province ?? '') == $province->id ? 'selected' : '' }}>
                {{ $province->province }}
            </option>
        @endforeach
    </select>
</div>

<button type="submit" class="btn btn-primary">Enregistrer</button>