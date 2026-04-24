<?php
// app/Http/Requests/Executions/StoreExecutionRequest.php

namespace App\Http\Requests\Executions;

use Illuminate\Foundation\Http\FormRequest;

class StoreExecutionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'id_jugement' => ['required', 'exists:jugements,id'],
            'date_notification' => ['required', 'date'],
            'date_execution' => ['nullable', 'date'],
            'observations' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_jugement.required'              => 'Veuillez sélectionner un jugement.',
            'numero_dossier_execution.required' => 'Le numéro de dossier d\'exécution est obligatoire.',
            'numero_dossier_execution.unique'   => 'Ce numéro d\'exécution existe déjà.',
            'date_notification.required'        => 'La date de notification est obligatoire.',
            'statut_execution.required'         => 'Le statut est obligatoire.',
            'date_execution.after_or_equal'     => 'La date d\'exécution doit être postérieure à la notification.',
            'responsable_id.required'           => 'Un responsable doit être désigné.',
        ];
    }
}
