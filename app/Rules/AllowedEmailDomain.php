<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class AllowedEmailDomain implements Rule
{
    /**
     * If the rule is being used for login (allow admin domain), set this flag.
     * @var bool
     */
    protected bool $forLogin = false;

    public function __construct(bool $forLogin = false)
    {
        $this->forLogin = $forLogin;
    }

    public function passes($attribute, $value): bool
    {
        if (! is_string($value) || strpos($value, '@') === false) {
            return false;
        }

        $domain = strtolower(substr(strrchr($value, '@'), 1));

        $allowed = config('allowed_emails.allowed_domains', []);
        $adminReserved = strtolower(config('allowed_emails.admin_reserved_domain', 'admin'));

        // exact match or subdomain allowed: allow user@sub.gmail.com if base gmail.com is allowed
        foreach ($allowed as $a) {
            $a = strtolower(trim($a));
            if ($a === '') continue;
            if ($domain === $a) return true;
            if (str_ends_with($domain, '.' . $a)) return true;
        }

        // if this is a login attempt, allow admin reserved domain to login (internal accounts)
        if ($this->forLogin && $adminReserved && ($domain === $adminReserved || str_ends_with($domain, '.' . $adminReserved))) {
            return true;
        }

        return false;
    }

    public function message(): string
    {
        $adminReserved = config('allowed_emails.admin_reserved_domain', 'admin');
        return "Alamat email tidak diizinkan. Gunakan email resmi (mis. gmail.com)";
    }
}
