<?php

namespace App\Http\Requests\Reclamations;

use App\Models\TypeJugement;
use Illuminate\Foundation\Http\FormRequest;

class ReclamationJugementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Accès protégé par le middleware auth du contrôleur.
    }

    public function rules(): array
    {
        return [
            'id_juge'            => 'nullable|exists:juges,id',
            'id_type_jugement'   => 'required|exists:type_jugements,id',
            'date_jugement'      => 'required|date|before_or_equal:today',
            'contenu_dispositif' => 'required|string|min:10',
            'est_definitif'      => 'boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'est_definitif' => $this->boolean('est_definitif'),

            'id_type_jugement' => $this->input('id_type_jugement')
                ?: TypeJugement::idAuFond(),
        ]);
    }

    /** رسائل التحقق باللغة العربية. */
    public function messages(): array
    {
        return [
            'id_juge.exists'                     => 'القاضي المحدد غير موجود.',
            'id_type_jugement.required'          => 'يرجى تحديد نوع الحكم.',
            'id_type_jugement.exists'            => 'نوع الحكم المحدد غير موجود.',
            'date_jugement.required'             => 'يرجى إدخال تاريخ الحكم.',
            'date_jugement.date'                 => 'يرجى إدخال تاريخ صحيح للحكم.',
            'date_jugement.before_or_equal'      => 'لا يمكن أن يكون تاريخ الحكم في المستقبل.',
            'contenu_dispositif.required'        => 'يرجى إدخال منطوق الحكم.',
            'contenu_dispositif.string'          => 'يجب أن يكون منطوق الحكم نصًا صالحًا.',
            'contenu_dispositif.min'             => 'يجب ألا يقل منطوق الحكم عن 10 أحرف.',
            'est_definitif.boolean'              => 'قيمة تحديد نهائية الحكم غير صالحة.',
        ];
    }

    /** أسماء الحقول باللغة العربية. */
    public function attributes(): array
    {
        return [
            'id_juge'            => 'القاضي',
            'id_type_jugement'   => 'نوع الحكم',
            'date_jugement'      => 'تاريخ الحكم',
            'contenu_dispositif' => 'منطوق الحكم',
            'est_definitif'      => 'نهائية الحكم',
        ];
    }
}
