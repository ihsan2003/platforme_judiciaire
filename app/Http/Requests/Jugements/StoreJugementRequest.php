<?php
// app/Http/Requests/Jugements/StoreJugementRequest.php

namespace App\Http\Requests\Jugements;

use Illuminate\Foundation\Http\FormRequest;

class StoreJugementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_dossier_tribunal' => 'required|exists:dossier_tribunaux,id',
            'id_juge'             => 'required|exists:juges,id',
            'date_jugement'       => 'required|date|before_or_equal:today',
            'contenu_dispositif'  => 'required|string|min:10',
            'est_definitif'       => 'boolean',
            'parties'             => 'nullable|array',
            'parties.*'           => 'exists:parties,id',
            'montants'            => 'nullable|array',
            'montants.*'          => 'nullable|numeric|min:0',
            'position_institution_etab' => ['nullable', 'exists:position_institutions,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_dossier_tribunal.required' => 'يرجى اختيار الملف/المحكمة.',
            'id_dossier_tribunal.exists'   => 'الملف أو المحكمة المحددة غير موجودة.',

            'id_juge.required'             => 'القاضي مطلوب.',
            'id_juge.exists'               => 'القاضي المحدد غير موجود.',

            'date_jugement.required'       => 'تاريخ الحكم مطلوب.',
            'date_jugement.date'           => 'يجب أن يكون تاريخ الحكم صالحًا.',
            'date_jugement.before_or_equal'=> 'لا يمكن أن يكون تاريخ الحكم في المستقبل.',

            'contenu_dispositif.required'  => 'منطوق الحكم مطلوب.',
            'contenu_dispositif.string'    => 'يجب أن يكون منطوق الحكم نصًا.',
            'contenu_dispositif.min'       => 'يجب أن يحتوي منطوق الحكم على 10 أحرف على الأقل.',

            'parties.array'               => 'يجب أن تكون الأطراف في شكل قائمة.',
            'parties.*.exists'            => 'أحد الأطراف المحددة غير موجود.',

            'montants.array'              => 'يجب أن تكون المبالغ في شكل قائمة.',
            'montants.*.numeric'          => 'يجب أن يكون المبلغ رقمًا.',
            'montants.*.min'              => 'لا يمكن أن يكون المبلغ سالبًا.',

            'position_institution_etab.exists' => 'المنصب المحدد غير موجود.',
        ];
    }

    public function attributes(): array
    {
        return [
            'id_dossier_tribunal' => 'الملف القضائي',
            'id_juge' => 'القاضي',
            'date_jugement' => 'تاريخ الحكم',
            'contenu_dispositif' => 'منطوق الحكم',
            'position_institution_etab' => 'المنصب',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'est_definitif' => $this->boolean('est_definitif'),
        ]);
    }
}
