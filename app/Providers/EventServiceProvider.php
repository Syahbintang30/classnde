<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Auth\Events\Registered;
use App\Listeners\GiveFreeCoachingTicket;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            GiveFreeCoachingTicket::class,
        ],
    ];
}
