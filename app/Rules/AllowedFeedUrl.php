<?php

namespace App\Rules;

use App\Support\PublicUrl;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class AllowedFeedUrl implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $reason = PublicUrl::rejectionReason(is_string($value) ? $value : null);

        if ($reason !== null) {
            $fail($reason);
        }
    }
}
