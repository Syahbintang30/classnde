<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NotDisposableEmail implements Rule
{
    /**
     * List of common disposable email domains. Expand as needed.
     * This is a lightweight, offline check — for production consider
     * combining with an external validation API for higher accuracy.
     *
     * @var string[]
     */
    protected array $blocked = [
        'mailinator.com',
        '10minutemail.com',
        'temp-mail.org',
        'tempmail.com',
        'guerrillamail.com',
        'yopmail.com',
        'maildrop.cc',
        'trashmail.com',
        'dispostable.com',
        'spamgourmet.com',
        'mailnesia.com',
        'fakeinbox.com',
        'getnada.com',
        'nowmymail.com',
    ];

    /**
     * Determine if the validation rule passes.
     */
    public function passes($attribute, $value): bool
    {
        if (! is_string($value) || strpos($value, '@') === false) {
            return false;
        }

        $domain = strtolower(substr(strrchr($value, '@'), 1));

        // exact match or subdomain match
        foreach ($this->blocked as $bad) {
            if ($domain === $bad) {
                return false;
            }
            // allow blocking of subdomains like tmp.mailinator.com
            if (str_ends_with($domain, '.' . $bad)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'Alamat email dari domain sekali pakai (disposable) tidak diperbolehkan. Gunakan email yang valid.';
    }
}
