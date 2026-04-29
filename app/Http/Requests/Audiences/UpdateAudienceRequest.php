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
        $audience = $this->route('audience'); // récupère le modèle ou l'ID
        $audienceId = is_object($audience) ? $audience->id : $audience;

        return [
            'id_dossier_tribunal' => 'required|exists:dossier_tribunaux,id',
            'id_type_audience'    => 'required|exists:type_audiences,id',
            'id_juge'             => 'required|exists:juges,id',
            'date_audience'       => [
                'required',
                'date',
                function (string $attribute, mixed $value, \Closure $fail) use ($audienceId) {

                    $idDossierTribunal = $this->input('id_dossier_tribunal');
                    if (! $idDossierTribunal) return;

                    $existe = \App\Models\Audience::where('id_dossier_tribunal', $idDossierTribunal)
                        ->whereDate('date_audience', $value)
                        ->where('id', '!=', $audienceId)
                        ->exists();

                    if ($existe) {
                        $fail('Une audience existe déjà à cette date pour ce dossier/tribunal.');
                    }
                },
            ],
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
