<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class OperatingHour implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (count($value) !== 7) {
            $fail("Invalid $attribute data.");
        }

        foreach ($value as $day => $hours) {
            [$open, $close] = $hours;

            if ($open === null && $close === null) {
                continue;
            }

            if ($open === null || $close === null) {
                $fail("Invalid $attribute data.");
            }

            if (strlen($open) !== 5 || strlen($close) !== 5) {
                $fail("Invalid $attribute data.");
            }

            $openTime = (int)str_replace(':', '', $open);
            $closeTime = (int)str_replace(':', '', $close);

            if ($openTime >= $closeTime) {
                $fail("Invalid $attribute data.");
            }

            if ($openTime > 2400 || $closeTime > 2400) {
                $fail("Invalid $attribute data.");
            }
        }
    }
}
