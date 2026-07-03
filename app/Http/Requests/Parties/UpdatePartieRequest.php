<?php

namespace App\Http\Requests\Parties;
use App\Rules\Telephone;


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
            'type_personne'     => ['required', 'in:ذاتي,اعتباري'],
            'identifiant_unique'=> ['nullable', 'string', "unique:parties,identifiant_unique,{$partieId}"],
            'date_naissance' => ['nullable', 'date'],
            'telephone'          => ['nullable', new Telephone],
            'email'             => ['nullable', 'email', 'max:255'],
            'adresse'           => ['nullable', 'string'],
            'id_avocat' => ['nullable', 'exists:avocats,id'],

        ];
    }
}