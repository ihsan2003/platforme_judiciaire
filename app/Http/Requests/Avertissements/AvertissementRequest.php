<?php

namespace App\Http\Requests\Avertissements;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation commune à la création et à la modification d'un avertissement.
 */
class AvertissementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // l'autorisation est gérée par AvertissementPolicy
    }

    public function rules(): array
    {
        return [
            'objet_litige'    => 'nullable|string|max:5000',
            'id_type_affaire' => 'required|exists:type_affaires,id',
            'id_province'     => 'required|exists:provinces,id',
            'id_tribunal'     => 'required|exists:tribunaux,id',
            'date_avertissement' => 'required|date|before_or_equal:today',
            'action_service'  => 'nullable|string|max:5000',
            'commentaire'     => 'nullable|string|max:5000',
            'id_statut_avertissement' => 'nullable|exists:statut_avertissements,id',
            'id_demandeur'    => 'required|exists:parties,id',
            'id_defendeur'    => 'required|exists:parties,id|different:id_demandeur',
        ];
    }

    public function messages(): array
    {
        return [
            'id_type_affaire.required' => 'يرجى اختيار نوعية الملف.',
            'id_type_affaire.exists'   => 'نوعية الملف غير صالحة.',
            'id_province.required'     => 'يرجى اختيار الإقليم / العمالة.',
            'id_province.exists'       => 'الإقليم / العمالة غير صالح.',
            'id_statut_avertissement.exists' => 'الحالة غير صالحة.',
            'date_avertissement.date'            => 'يجب أن يكون تاريخ الإنذار صالحًا.',
            'date_avertissement.before_or_equal' => 'لا يمكن أن يكون تاريخ الإنذار في المستقبل.',
            'id_tribunal.required'     => 'يرجى اختيار المحكمة المختصة.',
            'id_tribunal.exists'       => 'المحكمة المختارة غير صالحة.',
            'id_demandeur.exists'      => 'المدعي غير صالح.',
            'id_defendeur.exists'      => 'المدعى عليه غير صالح.',
            'id_defendeur.different'   => 'لا يمكن أن يكون المدعي والمدعى عليه نفس الطرف.',
        ];
    }

    public function attributes(): array
    {
        return [
            'objet_litige'    => 'موضوع النزاع',
            'id_type_affaire' => 'نوعية الملف',
            'id_province'     => 'الإقليم / العمالة',
            'id_tribunal'     => 'المحكمة المختصة',
            'action_service'  => 'الإجراء المتخذ من طرف مصلحة المنازعات والشؤون القانونية',
            'commentaire'     => 'ملاحظات',
            'id_statut_avertissement' => 'الحالة',
            'date_avertissement' => 'تاريخ الإنذار',
            'id_demandeur'    => 'المدعي',
            'id_defendeur'    => 'المدعى عليه',
        ];
    }
}
