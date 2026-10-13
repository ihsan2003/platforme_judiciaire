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
            'id_jugement.required' =>
                'يرجى اختيار الحكم.',

            'id_jugement.exists' =>
                'الحكم المحدد غير موجود.',

            'numero_dossier_execution.required' =>
                'رقم ملف التنفيذ إلزامي.',

            'numero_dossier_execution.unique' =>
                'رقم التنفيذ هذا مسجل بالفعل.',

            'date_notification.required' =>
                'تاريخ التبليغ إلزامي.',

            'date_notification.date' =>
                'يرجى إدخال تاريخ صحيح للتبليغ.',

            'date_execution.date' =>
                'يرجى إدخال تاريخ صحيح للتنفيذ.',

            'date_execution.after_or_equal' =>
                'يجب أن يكون تاريخ التنفيذ لاحقاً لتاريخ التبليغ أو مساوياً له.',

            'statut_execution.required' =>
                'حالة التنفيذ إلزامية.',

            'responsable_id.required' =>
                'يجب تعيين مسؤول عن التنفيذ.',

            'observations.string' =>
                'يجب أن تكون الملاحظات نصاً.',
        ];
    }
}