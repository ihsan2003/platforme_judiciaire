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
            'date_execution' => ['nullable', 'date', 'after_or_equal:date_notification'],
            'observations' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'statut_execution.required' =>
                'حالة التنفيذ إلزامية.',

            'statut_execution.exists' =>
                'حالة التنفيذ المحددة غير موجودة.',

            'date_notification.required' =>
                'تاريخ التبليغ إلزامي.',

            'date_notification.date' =>
                'يرجى إدخال تاريخ صحيح للتبليغ.',

            'date_execution.date' =>
                'يرجى إدخال تاريخ صحيح للتنفيذ.',

            'date_execution.after_or_equal' =>
                'يجب أن يكون تاريخ التنفيذ مساوياً لتاريخ التبليغ أو لاحقاً له.',

            'observations.string' =>
                'يجب أن تكون الملاحظات نصاً.',
        ];
    }
}