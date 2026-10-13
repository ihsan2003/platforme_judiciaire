<?php

namespace App\Http\Requests\Parties;

use App\Rules\Telephone;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePartieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // استثناء سجل الطرف الحالي عند التحقق من عدم تكرار المعرف
        $partie = $this->route('partie');
        $partieId = is_object($partie) ? $partie->id : $partie;

        return [
            'nom_partie' => [
                'required',
                'string',
                'max:255',
            ],

            'type_personne' => [
                'required',
                'in:ذاتي,اعتباري',
            ],

            'identifiant_unique' => [
                'nullable',
                'string',
                "unique:parties,identifiant_unique,{$partieId}",
            ],

            'date_naissance' => [
                'nullable',
                'date',
            ],

            'telephone' => [
                'nullable',
                new Telephone,
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'adresse' => [
                'nullable',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nom_partie.required' =>
                'اسم الطرف إلزامي.',

            'nom_partie.string' =>
                'يجب أن يكون اسم الطرف نصاً.',

            'nom_partie.max' =>
                'يجب ألا يتجاوز اسم الطرف 255 حرفاً.',

            'type_personne.required' =>
                'نوع الشخص إلزامي.',

            'type_personne.in' =>
                'نوع الشخص المحدد غير صحيح. يجب اختيار شخص ذاتي أو شخص اعتباري.',

            'identifiant_unique.string' =>
                'يجب أن يكون المعرف الفريد نصاً.',

            'identifiant_unique.unique' =>
                'هذا المعرف مسجل بالفعل لطرف آخر.',

            'date_naissance.date' =>
                'يرجى إدخال تاريخ ميلاد صحيح.',

            'email.email' =>
                'يرجى إدخال عنوان بريد إلكتروني صحيح.',

            'email.max' =>
                'يجب ألا يتجاوز عنوان البريد الإلكتروني 255 حرفاً.',

            'adresse.string' =>
                'يجب أن يكون العنوان نصاً.',
        ];
    }
}