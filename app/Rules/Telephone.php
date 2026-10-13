<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Telephone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // الصيغ المقبولة:
        // 06XXXXXXXX، 07XXXXXXXX، 05XXXXXXXX (10 أرقام)
        // +212XXXXXXXXX، 00212XXXXXXXXX

        $pattern = '/^(\+212|00212|0)(5|6|7)[0-9]{8}$/';

        if (! preg_match($pattern, preg_replace('/[\s\-\.]/', '', $value))) {
            $fail('يجب إدخال رقم هاتف مغربي صحيح (مثال: 0612345678 أو +212612345678).');
        }
    }
}