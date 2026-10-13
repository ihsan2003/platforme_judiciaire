<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * قواعد التحقق من صحة البيانات عند تحديث الملف الشخصي.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' =>
                'الاسم إلزامي.',

            'name.string' =>
                'يجب أن يكون الاسم نصاً.',

            'name.max' =>
                'يجب ألا يتجاوز الاسم 255 حرفاً.',

            'email.required' =>
                'البريد الإلكتروني إلزامي.',

            'email.string' =>
                'يجب أن يكون البريد الإلكتروني نصاً.',

            'email.lowercase' =>
                'يجب إدخال البريد الإلكتروني بأحرف صغيرة.',

            'email.email' =>
                'يرجى إدخال عنوان بريد إلكتروني صحيح.',

            'email.max' =>
                'يجب ألا يتجاوز البريد الإلكتروني 255 حرفاً.',

            'email.unique' =>
                'عنوان البريد الإلكتروني مستخدم بالفعل من قبل مستخدم آخر.',
        ];
    }
}