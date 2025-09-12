<?php

return [
    // Optional list of coach emails who can join any session (fallback if coach_user_id not set)
    'coaches' => env('COACH_EMAILS', '') !== '' ? array_map('trim', explode(',', env('COACH_EMAILS', ''))) : [],
    // which package slugs make a user eligible to buy a coaching ticket
    // Admin can set package slugs like ['beginner','intermediate']
    'eligible_packages' => env('COACHING_ELIGIBLE_PACKAGES', '') !== '' ? array_map('trim', explode(',', env('COACHING_ELIGIBLE_PACKAGES', ''))) : ['beginner','intermediate'],
    // package slug used to represent the coaching ticket price (admin can create/edit this package)
    'coaching_package_slug' => env('COACHING_PACKAGE_SLUG', 'coaching-ticket'),
];
