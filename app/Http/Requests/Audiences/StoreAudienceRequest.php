<?php
// app/Http/Requests/Audiences/StoreAudienceRequest.php

namespace App\Http\Requests\Audiences;

use Illuminate\Foundation\Http\FormRequest;

class StoreAudienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // affiner avec une Policy si besoin
    }

    public function rules(): array
    {
        return [
            'id_dossier_tribunal'     => 'required|exists:dossier_tribunaux,id',
            'id_type_audience'        => 'required|exists:type_audiences,id',
            'id_juge'                 => 'required|exists:juges,id',
            'date_audience'           => 'required|date',
            'date_prochaine_audience' => 'nullable|date|after_or_equal:date_audience',
            'presence_demandeur'      => 'boolean',
            'presence_defendeur'      => 'boolean',
            'resultat_audience'       => 'nullable|string|max:2000',
            'actions_demandees'       => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'id_dossier_tribunal.required' => 'Veuillez sélectionner un dossier.',
            'id_type_audience.required'    => "Le type d'audience est obligatoire.",
            'id_juge.required'             => 'Veuillez désigner un juge.',
            'date_audience.required'       => "La date d'audience est obligatoire.",
            'date_prochaine_audience.after_or_equal' => 'La prochaine audience doit être postérieure à l\'audience en cours.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normaliser les checkboxes (non cochées = false)
        $this->merge([
            'presence_demandeur' => $this->boolean('presence_demandeur'),
            'presence_defendeur' => $this->boolean('presence_defendeur'),
        ]);
    }
}
