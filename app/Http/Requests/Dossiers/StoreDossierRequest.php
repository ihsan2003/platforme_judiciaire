<?php

namespace App\Http\Requests\Dossiers;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation des données pour la création d'un dossier judiciaire.
 */
class StoreDossierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'numero_dossier_tribunal' => [
                'nullable',
                'string',
                'regex:/^\d{4} \/ \d{4} \/ \d{1,6}$/'
            ],
            'id_type_affaire'         => 'required|exists:type_affaires,id',
            'date_ouverture'          => 'required|date',
            'date_cloture'            => 'nullable|date|after:date_ouverture',
        ];
    }

    public function messages(): array
    {
        return [
            // type affaire
            'id_type_affaire.required'       => 'يرجى اختيار نوع القضية.',
            'id_type_affaire.exists'         => 'نوع القضية غير صالح.',

            // dates
            'date_ouverture.required'        => 'تاريخ فتح الملف مطلوب.',
            'date_ouverture.date'            => 'تاريخ فتح الملف غير صالح.',

            'date_cloture.date'              => 'تاريخ الإغلاق غير صالح.',
            'date_cloture.after'             => 'يجب أن يكون تاريخ الإغلاق بعد تاريخ الفتح.',
            'numero_dossier_tribunal.regex'  => 'صيغة رقم المحكمة غير صحيحة. يجب أن تكون: السنة / رمز الفئة / الرقم (مثال: 2024 / 1201 / 450).',
        ];
    }

    public function attributes(): array
    {
        return [
            'numero_dossier_tribunal' => 'رقم الملف بالمحكمة',
            'id_type_affaire' => 'نوع القضية',
            'date_ouverture' => 'تاريخ فتح الملف',
            'date_cloture' => 'تاريخ الإغلاق',
        ];
    }
}