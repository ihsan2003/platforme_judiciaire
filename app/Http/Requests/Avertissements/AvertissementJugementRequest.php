<?php

namespace App\Http\Requests\Avertissements;

use Illuminate\Foundation\Http\FormRequest;

class AvertissementJugementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // autorisation via AvertissementPolicy@update dans le contrôleur
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
                ?: \App\Models\TypeJugement::idAuFond(),
        ]);
    }

    public function messages(): array
    {
        return [
            'id_juge.exists'                => 'القاضي المحدد غير موجود.',
            'id_type_jugement.required'     => 'يرجى تحديد نوع الحكم.',
            'id_type_jugement.exists'       => 'نوع الحكم المحدد غير موجود.',
            'date_jugement.required'        => 'تاريخ الحكم مطلوب.',
            'date_jugement.date'            => 'يجب أن يكون تاريخ الحكم صالحًا.',
            'date_jugement.before_or_equal' => 'لا يمكن أن يكون تاريخ الحكم في المستقبل.',
            'contenu_dispositif.required'   => 'منطوق الحكم مطلوب.',
            'contenu_dispositif.min'        => 'يجب أن يحتوي منطوق الحكم على 10 أحرف على الأقل.',
        ];
    }

    public function attributes(): array
    {
        return [
            'id_juge'            => 'القاضي',
            'id_type_jugement'   => 'نوع الحكم',
            'date_jugement'      => 'تاريخ الحكم',
            'contenu_dispositif' => 'منطوق الحكم',
        ];
    }
}
