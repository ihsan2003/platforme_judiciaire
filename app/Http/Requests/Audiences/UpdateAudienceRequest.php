<?php
// app/Http/Requests/Audiences/UpdateAudienceRequest.php

namespace App\Http\Requests\Audiences;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Audience;

class UpdateAudienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $audience = $this->route('audience');
        $audienceId = is_object($audience) ? $audience->id : $audience;

        return [
            'id_dossier_tribunal' => 'required|exists:dossier_tribunaux,id',

            'id_type_audience' => 'required|exists:type_audiences,id',

            'id_juge' => 'required|exists:juges,id',

            'date_audience' => [
                'required',
                'date',

                // منع تكرار الجلسة لنفس الملف وفي نفس التاريخ
                function (string $attribute, mixed $value, \Closure $fail) use ($audienceId) {

                    $idDossierTribunal = $this->input('id_dossier_tribunal');

                    if (! $idDossierTribunal) {
                        return;
                    }

                    $existe = Audience::where(
                        'id_dossier_tribunal',
                        $idDossierTribunal
                    )
                        ->whereDate('date_audience', $value)
                        ->where('id', '!=', $audienceId)
                        ->exists();

                    if ($existe) {
                        $fail('توجد جلسة مسجلة بالفعل في هذا التاريخ لهذا الملف بالمحكمة.');
                    }
                },
            ],

            'date_prochaine_audience' => 'nullable|date|after_or_equal:date_audience',

            'presence_demandeur' => 'boolean',

            'presence_defendeur' => 'boolean',

            'presence_avocat_entraide' => 'boolean',

            'resultat_audience' => 'nullable|string|max:2000',

            'actions_demandees' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'id_dossier_tribunal.required' =>
                'يرجى اختيار ملف القضية.',

            'id_dossier_tribunal.exists' =>
                'ملف القضية المحدد غير موجود.',

            'id_type_audience.required' =>
                'نوع الجلسة إلزامي.',

            'id_type_audience.exists' =>
                'نوع الجلسة المحدد غير موجود.',

            'id_juge.required' =>
                'يرجى تحديد القاضي.',

            'id_juge.exists' =>
                'القاضي المحدد غير موجود.',

            'date_audience.required' =>
                'تاريخ الجلسة إلزامي.',

            'date_audience.date' =>
                'يرجى إدخال تاريخ صحيح للجلسة.',

            'date_prochaine_audience.date' =>
                'يرجى إدخال تاريخ صحيح للجلسة المقبلة.',

            'date_prochaine_audience.after_or_equal' =>
                'يجب أن يكون تاريخ الجلسة المقبلة مساوياً لتاريخ الجلسة الحالية أو لاحقاً له.',

            'presence_demandeur.boolean' =>
                'قيمة حضور المدعي غير صحيحة.',

            'presence_defendeur.boolean' =>
                'قيمة حضور المدعى عليه غير صحيحة.',

            'presence_avocat_entraide.boolean' =>
                'قيمة حضور محامي المساعدة القضائية غير صحيحة.',

            'resultat_audience.string' =>
                'يجب أن تكون نتيجة الجلسة نصاً.',

            'resultat_audience.max' =>
                'يجب ألا تتجاوز نتيجة الجلسة 2000 حرف.',

            'actions_demandees.string' =>
                'يجب أن تكون الإجراءات المطلوبة نصاً.',

            'actions_demandees.max' =>
                'يجب ألا تتجاوز الإجراءات المطلوبة 2000 حرف.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'presence_demandeur' => $this->boolean('presence_demandeur'),

            'presence_defendeur' => $this->boolean('presence_defendeur'),

            'presence_avocat_entraide' => $this->boolean('presence_avocat_entraide'),
        ]);
    }
}