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

    public function rules(): array
    {
        return [
            'id_jugement'              => 'required|exists:jugements,id',
            'numero_dossier_execution' => 'required|string|max:255|unique:executions,numero_dossier_execution',
            'date_notification'        => 'required|date',
            'statut_execution'         => 'required|exists:statut_executions,id',
            'date_execution'           => 'nullable|date|after_or_equal:date_notification',
            'responsable_id'           => 'required|exists:users,id',
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
