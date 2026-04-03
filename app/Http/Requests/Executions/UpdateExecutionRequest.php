<?php
// app/Http/Requests/Executions/UpdateExecutionRequest.php

namespace App\Http\Requests\Executions;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExecutionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_jugement'              => 'required|exists:jugements,id',
            'numero_dossier_execution' => 'required|string|max:255|unique:executions,numero_dossier_execution,' . $this->execution->id,
            'date_notification'        => 'required|date',
            'statut_execution'         => 'required|exists:statut_executions,id',
            'date_execution'           => 'nullable|date|after_or_equal:date_notification',
            'responsable_id'           => 'required|exists:users,id',
        ];
    }
}
