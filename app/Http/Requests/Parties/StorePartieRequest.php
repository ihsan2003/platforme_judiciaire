<?php

namespace App\Http\Requests\Parties;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Telephone;

class StorePartieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom_partie'         => ['required', 'string', 'max:255'],
            'type_personne'      => ['required', 'in:Physique,Morale'],
            'identifiant_unique' => ['required', 'string', 'unique:parties,identifiant_unique'],
            'date_naissance'     => ['nullable', 'date'],
            'telephone'          => ['nullable', new Telephone],
            'email'              => ['nullable', 'email', 'max:255'],
            'adresse'            => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            // nom
            'nom_partie.required' => 'اسم الطرف مطلوب.',
            'nom_partie.string'   => 'اسم الطرف غير صالح.',

            // type personne
            'type_personne.required' => 'يرجى تحديد نوع الشخص.',
            'type_personne.in'       => 'نوع الشخص يجب أن يكون طبيعي أو معنوي.',

            // identifiant
            'identifiant_unique.required' => 'المعرف الفريد مطلوب.',
            'identifiant_unique.unique'   => 'هذا المعرف مستخدم بالفعل.',

            // date naissance
            'date_naissance.date' => 'تاريخ الميلاد غير صالح.',

            // email
            'email.email' => 'البريد الإلكتروني غير صالح.',
            'email.max'   => 'البريد الإلكتروني طويل جداً.',

            // adresse
            'adresse.string' => 'العنوان غير صالح.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nom_partie' => 'اسم الطرف',
            'type_personne' => 'نوع الشخص',
            'identifiant_unique' => 'المعرف الفريد',
            'date_naissance' => 'تاريخ الميلاد',
            'telephone' => 'الهاتف',
            'email' => 'البريد الإلكتروني',
            'adresse' => 'العنوان',
        ];
    }
}