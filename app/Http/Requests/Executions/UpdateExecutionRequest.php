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

    public function rules()
    {
        return [
            'statut_execution' => ['required', 'exists:statut_executions,id'],
            'date_notification' => ['required', 'date'],
            'date_execution' => ['nullable', 'date'],
            'observations' => ['nullable', 'string'],
        ];
    }
}
