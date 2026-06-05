<?php

namespace App\Http\Requests\Dossiers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDossierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $dossierId = $this->route('dossier')->id;

        return [
            'numero_dossier_interne'  => [
                'required', 'string', 'max:255',
                Rule::unique('dossier_judiciaires', 'numero_dossier_interne')->ignore($dossierId),
            ],
            'numero_dossier_tribunal' => ['nullable', 'string', 'max:255'],
            'id_type_affaire'         => ['required', 'exists:type_affaires,id'],
            'id_statut_dossier'       => ['required', 'exists:statut_dossiers,id'],
            'date_ouverture'          => ['required', 'date'],
            'date_cloture'            => ['nullable', 'date', 'after_or_equal:date_ouverture'],
        ];
    }

    public function messages(): array
    {
        return [
            // numéro dossier
            'numero_dossier_interne.required' => 'رقم الملف الداخلي مطلوب.',
            'numero_dossier_interne.unique'   => 'هذا الرقم مستخدم بالفعل من قبل ملف آخر.',

            // type affaire
            'id_type_affaire.required'        => 'يرجى اختيار نوع القضية.',
            'id_type_affaire.exists'          => 'نوع القضية غير صالح.',

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
            'numero_dossier_interne' => 'رقم الملف الداخلي',
            'numero_dossier_tribunal' => 'رقم الملف بالمحكمة',
            'id_type_affaire' => 'نوع القضية',
            'id_statut_dossier' => 'حالة الملف',
            'date_ouverture' => 'تاريخ فتح الملف',
            'date_cloture' => 'تاريخ الإغلاق',
        ];
    }
}