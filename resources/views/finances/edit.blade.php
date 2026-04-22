@extends('layouts.app')

@section('content')
<h1>Modifier Finance</h1>

<form method="POST" action="{{ route('finances.update', $finance) }}">
    @csrf
    @method('PUT')

    <label>Jugement</label>
    <select name="id_jugement">
        @foreach($jugements as $j)
            <option value="{{ $j->id }}"
                {{ $finance->id_jugement == $j->id ? 'selected' : '' }}>
                {{ $j->id }}
            </option>
        @endforeach
    </select>

    <label>Montant condamné</label>
    <input type="number" step="0.01" name="montant_condamne" value="{{ $finance->montant_condamne }}">

    <label>Montant payé</label>
    <input type="number" step="0.01" name="montant_paye" value="{{ $finance->montant_paye }}">

    <button type="submit">Modifier</button>
</form>
@endsection