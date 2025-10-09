<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
	/**
	 * The URIs that should be excluded from CSRF verification.
	 * SECURITY NOTE: These endpoints MUST have alternative security measures!
	 *
	 * @var array
	 */
	protected $except = [
		// Midtrans server-to-server notification (protected by signature validation & IP whitelist)
		'payments/midtrans-notify',           // relative path (common in Laravel)
		'/payments/midtrans-notify',          // absolute path (defensive, some setups expect leading slash)
		// Twilio webhooks (protected by signature validation & IP whitelist)  
		'webhooks/twilio/*',
	];
}

