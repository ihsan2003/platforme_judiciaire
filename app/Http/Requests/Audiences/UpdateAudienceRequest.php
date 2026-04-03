<?php
// app/Http/Requests/Audiences/UpdateAudienceRequest.php

namespace App\Http\Requests\Audiences;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAudienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_dossier_tribunal'     => 'required|exists:dossier_tribunaux,id',  // ← corrige 'dossier_tribunals'
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
        return (new StoreAudienceRequest())->messages();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'presence_demandeur' => $this->boolean('presence_demandeur'),
            'presence_defendeur' => $this->boolean('presence_defendeur'),
        ]);
    }
}
