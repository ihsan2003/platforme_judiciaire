<?php

namespace App\Http\Requests\Dossiers;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDossierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Le numéro n'est plus modifiable ici : il appartient à chaque instance
        // (modal « تعديل بيانات المحكمة » → DossierTribunalController::update).
        return [
            'id_type_affaire'         => ['required', 'exists:type_affaires,id'],
            'id_statut_dossier'       => ['required', 'exists:statut_dossiers,id'],
            'date_ouverture'          => ['required', 'date'],
            'date_cloture'            => ['nullable', 'date', 'after_or_equal:date_ouverture'],
            'objet_litige'            => ['nullable', 'string', 'max:5000'],
            'action_service'          => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            // type affaire
            'id_type_affaire.required'        => 'يرجى اختيار الشعبة.',
            'id_type_affaire.exists'          => 'الشعبة غير صالحة.',

            // statut
            'id_statut_dossier.required'      => 'يرجى اختيار حالة الملف.',
            'id_statut_dossier.exists'        => 'حالة الملف غير صالحة.',

            // dates
            'date_ouverture.required'         => 'تاريخ فتح الملف مطلوب.',
            'date_ouverture.date'             => 'تاريخ فتح الملف غير صالح.',

            'date_cloture.date'               => 'تاريخ الإغلاق غير صالح.',
            'date_cloture.after_or_equal'     => 'يجب أن يكون تاريخ الإغلاق بعد أو يساوي تاريخ الفتح.',
        ];
    }

    public function attributes(): array
    {
        return [
            'id_type_affaire' => 'الشعبة',
            'objet_litige' => 'موضوع النزاع',
            'action_service' => 'الإجراء المتخذ من طرف المصلحة',
            'id_statut_dossier' => 'حالة الملف',
            'date_ouverture' => 'تاريخ فتح الملف',
            'date_cloture' => 'تاريخ الإغلاق',
        ];
    }
}