<?php

namespace App\Http\Requests\Parties;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePartieRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        // ignore l'unicité pour l'enregistrement lui-même lors de l'update
        $partieId = $this->route('partie')->id;

        return [
            'nom_partie'        => ['required', 'string', 'max:255'],
            'type_personne'     => ['required', 'in:physique,morale'],
            'identifiant_unique'=> ['required', 'string', "unique:parties,identifiant_unique,{$partieId}"],
            'telephone'         => ['nullable', 'string', 'max:20'],
            'email'             => ['nullable', 'email', 'max:255'],
            'adresse'           => ['nullable', 'string'],
        ];
    }
}