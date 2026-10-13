<?php
// app/Http/Requests/Jugements/UpdateJugementRequest.php

namespace App\Http\Requests\Jugements;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJugementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_dossier_tribunal' => 'required|exists:dossier_tribunaux,id',
            'id_juge' => 'required|exists:juges,id',
            'date_jugement' => 'required|date|before_or_equal:today',
            'contenu_dispositif' => 'required|string|min:10',
            'est_definitif' => 'boolean',
            'parties' => 'nullable|array',
            'parties.*' => 'exists:parties,id',
            'montants' => 'nullable|array',
            'montants.*' => 'nullable|numeric|min:0',
            'position_institution_etab' => [
                'nullable',
                'exists:position_institutions,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'id_dossier_tribunal.required' =>
                'يرجى اختيار ملف القضية.',

            'id_dossier_tribunal.exists' =>
                'ملف القضية المحدد غير موجود.',

            'id_juge.required' =>
                'يرجى تحديد القاضي.',

            'id_juge.exists' =>
                'القاضي المحدد غير موجود.',

            'date_jugement.required' =>
                'تاريخ الحكم إلزامي.',

            'date_jugement.date' =>
                'يرجى إدخال تاريخ صحيح للحكم.',

            'date_jugement.before_or_equal' =>
                'يجب ألا يتجاوز تاريخ الحكم تاريخ اليوم.',

            'contenu_dispositif.required' =>
                'محتوى منطوق الحكم إلزامي.',

            'contenu_dispositif.string' =>
                'يجب أن يكون محتوى منطوق الحكم نصاً.',

            'contenu_dispositif.min' =>
                'يجب ألا يقل محتوى منطوق الحكم عن 10 أحرف.',

            'est_definitif.boolean' =>
                'قيمة نهائية الحكم غير صحيحة.',

            'parties.array' =>
                'يجب أن تكون الأطراف المحددة في شكل قائمة.',

            'parties.*.exists' =>
                'أحد الأطراف المحددة غير موجود.',

            'montants.array' =>
                'يجب أن تكون المبالغ في شكل قائمة.',

            'montants.*.numeric' =>
                'يجب أن تكون قيمة المبلغ رقماً.',

            'montants.*.min' =>
                'يجب ألا يكون المبلغ أقل من صفر.',

            'position_institution_etab.exists' =>
                'المؤسسة المحددة غير موجودة.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'est_definitif' => $this->boolean('est_definitif'),
        ]);
    }
}