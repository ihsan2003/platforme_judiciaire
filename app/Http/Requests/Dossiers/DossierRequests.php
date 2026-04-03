<?php
// ============================================================
// app/Http/Requests/Dossiers/StoreDossierRequest.php
// ============================================================

namespace App\Http\Requests\Dossiers;

use Illuminate\Foundation\Http\FormRequest;

class StoreDossierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La vérification se fait via authorizeResource() dans le contrôleur
    }

    public function rules(): array
    {
        return [
            'numero_dossier_interne'  => ['required', 'string', 'max:255', 'unique:dossier_judiciaires,numero_dossier_interne'],
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
            'numero_dossier_interne.required' => 'Le numéro de dossier interne est obligatoire.',
            'numero_dossier_interne.unique'   => 'Ce numéro de dossier existe déjà dans le système.',
            'id_type_affaire.required'        => 'Veuillez sélectionner le type d\'affaire.',
            'id_type_affaire.exists'          => 'Le type d\'affaire sélectionné est invalide.',
            'id_statut_dossier.required'      => 'Veuillez sélectionner un statut.',
            'date_ouverture.required'         => 'La date d\'ouverture est obligatoire.',
            'date_cloture.after_or_equal'     => 'La date de clôture doit être postérieure ou égale à la date d\'ouverture.',
        ];
    }

    public function attributes(): array
    {
        return [
            'numero_dossier_interne'  => 'numéro interne',
            'numero_dossier_tribunal' => 'numéro tribunal',
            'id_type_affaire'         => 'type d\'affaire',
            'id_statut_dossier'       => 'statut',
            'date_ouverture'          => 'date d\'ouverture',
            'date_cloture'            => 'date de clôture',
        ];
    }
}


// ============================================================
// app/Http/Requests/Dossiers/UpdateDossierRequest.php
// ============================================================

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
            'numero_dossier_interne.unique'  => 'Ce numéro de dossier est déjà utilisé par un autre dossier.',
            'date_cloture.after_or_equal'    => 'La date de clôture doit être postérieure ou égale à la date d\'ouverture.',
        ];
    }
}
