<?php

namespace App\Http\Requests\Dossiers;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation des données pour la création d'un dossier judiciaire.
 */
class StoreDossierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ou vérification de permission ici
    }

    public function rules(): array
    {
        return [
            'numero_dossier_interne'   => 'required|string|unique:dossier_judiciaires',
            'numero_dossier_tribunal'  => 'nullable|string',
            'id_type_affaire'          => 'required|exists:type_affaires,id',
            'date_ouverture'           => 'required|date',
            'date_cloture'             => 'nullable|date|after:date_ouverture',
        ];
    }

    public function messages(): array
    {
        return [
            'numero_dossier_interne.unique' => 'Ce numéro de dossier existe déjà.',
            'date_cloture.after'            => 'La date de clôture doit être après la date d\'ouverture.',
        ];
    }
}