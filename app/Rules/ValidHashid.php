<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Vinkla\Hashids\Facades\Hashids;

class ValidHashid implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $decoded = Hashids::decode($value);
        if (empty($decoded)) {
            $fail("The {$attribute} is not a valid hashid.", null);
        }
    }
}
