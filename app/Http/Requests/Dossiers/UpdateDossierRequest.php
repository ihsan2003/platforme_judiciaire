<?php

namespace App\Http\Requests\Dossiers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDossierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $dossierId = $this->route('dossier')->id;

        return [
            'numero_dossier_interne'  => [
                'required', 'string', 'max:255',
                Rule::unique('dossier_judiciaires', 'numero_dossier_interne')->ignore($dossierId),
            ],
            'numero_dossier_tribunal' => ['nullable', 'string', 'max:255'],
            'id_type_affaire'         => ['required', 'exists:type_affaires,id'],
            'id_statut_dossier'       => ['required', 'exists:statut_dossiers,id'],
            'date_ouverture'          => ['required', 'date'],
            'date_cloture'            => ['nullable', 'date', 'after_or_equal:date_ouverture'],
        ];
    }

    public function messages(): array
    {
        return [
            'numero_dossier_interne.unique' => 'Ce numéro de dossier est déjà utilisé par un autre dossier.',
            'date_cloture.after_or_equal'   => 'La date de clôture doit être postérieure ou égale à la date d\'ouverture.',
        ];
    }
}
