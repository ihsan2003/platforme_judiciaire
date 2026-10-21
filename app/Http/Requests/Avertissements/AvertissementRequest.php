<?php

namespace App\Http\Requests\Avertissements;

use App\Models\StatutAvertissement;
use Illuminate\Foundation\Http\FormRequest;

class AvertissementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'objet_litige' => 'nullable|string|max:5000',

            'id_type_affaire' => 'required|exists:type_affaires,id',

            'id_province' => 'required|exists:provinces,id',

            'id_tribunal' => 'required|exists:tribunaux,id',

            'date_avertissement' => 'required|date|before_or_equal:today',

            'action_service' => 'nullable|string|max:5000',

            'commentaire' => 'nullable|string|max:5000',

            'id_statut_avertissement' => [
                'nullable',
                'exists:statut_avertissements,id',

                function (string $attribute, mixed $value, \Closure $fail) {
                    $idClassement = StatutAvertissement::idClassement();

                    $idCourant = $this->route('avertissement')
                        ?->id_statut_avertissement;

                    // حالة الحفظ تُطبَّق تلقائيًا بعد صدور حكم نهائي.
                    if (
                        $idClassement
                        && (int) $value === (int) $idClassement
                        && (int) $idCourant !== (int) $idClassement
                    ) {
                        $fail(
                            'لا يمكن اختيار حالة «حفظ» يدويًا، '
                            . 'إذ يتم تطبيقها تلقائيًا بعد صدور حكم نهائي.'
                        );
                    }
                },
            ],

            'id_demandeur' => 'required|exists:parties,id',

            'id_defendeur' => 'required|exists:parties,id|different:id_demandeur',
        ];
    }

    /**
     * رسائل التحقق باللغة العربية.
     */
    public function messages(): array
    {
        return [
            // موضوع النزاع
            'objet_litige.string' => 'يجب أن يكون موضوع النزاع نصًا صالحًا.',
            'objet_litige.max' => 'يجب ألا يتجاوز موضوع النزاع 5000 حرف.',

            // نوعية الملف
            'id_type_affaire.required' => 'يرجى اختيار نوعية الملف.',
            'id_type_affaire.exists' => 'نوعية الملف المختارة غير صالحة.',

            // الإقليم أو العمالة
            'id_province.required' => 'يرجى اختيار الإقليم أو العمالة.',
            'id_province.exists' => 'الإقليم أو العمالة المختارة غير صالحة.',

            // المحكمة المختصة
            'id_tribunal.required' => 'يرجى اختيار المحكمة المختصة.',
            'id_tribunal.exists' => 'المحكمة المختارة غير صالحة.',

            // تاريخ الإنذار
            'date_avertissement.required' => 'يرجى تحديد تاريخ الإنذار.',
            'date_avertissement.date' => 'يرجى إدخال تاريخ إنذار صالح.',
            'date_avertissement.before_or_equal' => 'لا يمكن أن يكون تاريخ الإنذار في المستقبل.',

            // الإجراء المتخذ
            'action_service.string' => 'يجب أن يكون الإجراء المتخذ نصًا صالحًا.',
            'action_service.max' => 'يجب ألا يتجاوز الإجراء المتخذ 5000 حرف.',

            // الملاحظات
            'commentaire.string' => 'يجب أن تكون الملاحظات نصًا صالحًا.',
            'commentaire.max' => 'يجب ألا تتجاوز الملاحظات 5000 حرف.',

            // حالة الإنذار
            'id_statut_avertissement.exists' => 'حالة الإنذار المختارة غير صالحة.',

            // المدعي
            'id_demandeur.required' => 'يرجى اختيار المدعي.',
            'id_demandeur.exists' => 'المدعي المختار غير موجود.',

            // المدعى عليه
            'id_defendeur.required' => 'يرجى اختيار المدعى عليه.',
            'id_defendeur.exists' => 'المدعى عليه المختار غير موجود.',
            'id_defendeur.different' => 'لا يمكن أن يكون المدعي والمدعى عليه نفس الطرف.',
        ];
    }

    /**
     * أسماء الحقول باللغة العربية.
     */
    public function attributes(): array
    {
        return [
            'objet_litige' => 'موضوع النزاع',
            'id_type_affaire' => 'نوعية الملف',
            'id_province' => 'الإقليم / العمالة',
            'id_tribunal' => 'المحكمة المختصة',
            'date_avertissement' => 'تاريخ الإنذار',
            'action_service' => 'الإجراء المتخذ من طرف مصلحة المنازعات والشؤون القانونية',
            'commentaire' => 'ملاحظات',
            'id_statut_avertissement' => 'حالة الإنذار',
            'id_demandeur' => 'المدعي',
            'id_defendeur' => 'المدعى عليه',
        ];
    }
}
