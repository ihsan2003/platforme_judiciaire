<?php

namespace App\Http\Requests\Parties;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Telephone;

class StorePartieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ou une vérification de permission
    }

    public function rules(): array
    {
        return [
            'nom_partie'        => ['required', 'string', 'max:255'],
            'type_personne'     => ['required', 'in:physique,morale'],
            'identifiant_unique'=> ['required', 'string', 'unique:parties,identifiant_unique'],
            'telephone'          => ['nullable', new Telephone],
            'email'             => ['nullable', 'email', 'max:255'],
            'adresse'           => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom_partie.required'         => 'Le nom de la partie est obligatoire.',
            'identifiant_unique.unique'   => 'Cet identifiant existe déjà.',
            'type_personne.in'            => 'Le type doit être physique ou morale.',
        ];
    }
}