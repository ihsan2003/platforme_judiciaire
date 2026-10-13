<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * التحقق من صلاحية تنفيذ الطلب.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * قواعد التحقق من صحة بيانات تسجيل الدخول.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * رسائل التحقق باللغة العربية.
     */
    public function messages(): array
    {
        return [
            'email.required' =>
                'البريد الإلكتروني إلزامي.',

            'email.string' =>
                'يجب أن يكون البريد الإلكتروني نصاً.',

            'email.email' =>
                'يرجى إدخال عنوان بريد إلكتروني صحيح.',

            'password.required' =>
                'كلمة المرور إلزامية.',

            'password.string' =>
                'يجب أن تكون كلمة المرور نصاً.',
        ];
    }

    /**
     * محاولة التحقق من بيانات تسجيل الدخول.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(
            $this->only('email', 'password'),
            $this->boolean('remember')
        )) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * التحقق من عدم تجاوز الحد المسموح لمحاولات تسجيل الدخول.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        $minutes = ceil($seconds / 60);

        throw ValidationException::withMessages([
            'email' => "لقد تجاوزت عدد محاولات تسجيل الدخول المسموح بها. يرجى المحاولة بعد {$minutes} دقيقة.",
        ]);
    }

    /**
     * إنشاء المفتاح المستخدم لتحديد عدد محاولات تسجيل الدخول.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower($this->string('email')).'|'.$this->ip()
        );
    }
}