<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Allowed email domains
    |--------------------------------------------------------------------------
    |
    | A whitelist of domains allowed to register/login on the site. By
    | default we permit Gmail and the company's official domain. You can
    | override this in production via environment variables by setting
    | a comma-separated list in ALLOWED_EMAIL_DOMAINS.
    |
    */
    'allowed_domains' => array_filter(array_map('trim', explode(',', env('ALLOWED_EMAIL_DOMAINS', 'gmail.com,ndeofficial.com')))),

    /*
    |--------------------------------------------------------------------------
    | Admin reserved domain
    |--------------------------------------------------------------------------
    |
    | A special domain name reserved for admin accounts. Public registration
    | using this domain will be blocked. Admin accounts should be created
    | through the internal admin tools or seeder.
    |
    */
    'admin_reserved_domain' => env('ADMIN_RESERVED_DOMAIN', 'admin'),
];
