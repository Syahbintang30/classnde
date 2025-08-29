<?php

namespace App\Services;

use Twilio\Rest\Client as TwilioClient;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VideoGrant;

class TwilioService
{
	protected $accountSid;
	protected $authToken;
	protected $apiKeySid;
	protected $apiKeySecret;
	protected $client;

	public function __construct()
	{
		$this->accountSid = config('services.twilio.account_sid');
		$this->authToken = config('services.twilio.auth_token');
		$this->apiKeySid = config('services.twilio.api_key_sid');
		$this->apiKeySecret = config('services.twilio.api_key_secret');
	}

	public function isConfigured(): bool
	{
		return (bool) ($this->accountSid && $this->apiKeySid && $this->apiKeySecret);
	}

	public function getClient(): ?TwilioClient
	{
		if ($this->client) return $this->client;
		if (! $this->accountSid) return null;

		// Prefer auth token, fallback to api key/secret if auth token not present
		if ($this->authToken) {
			$this->client = new TwilioClient($this->accountSid, $this->authToken);
		} elseif ($this->apiKeySid && $this->apiKeySecret) {
			$this->client = new TwilioClient($this->apiKeySid, $this->apiKeySecret, $this->accountSid);
		} else {
			return null;
		}
		return $this->client;
	}

	/**
	 * Create or fetch a Twilio Video Room by uniqueName.
	 * Throws exceptions from the Twilio client up to caller.
	 */
	public function createOrFetchRoom(string $roomName, array $options = [])
	{
		// In testing environment, avoid calling external Twilio API
		if (app()->environment('testing')) {
			return (object) ['sid' => 'RMFAKE' . rand(1000,9999), 'uniqueName' => $roomName];
		}
		$client = $this->getClient();
		if (! $client) {
			throw new \RuntimeException('Twilio client not configured');
		}

		// try to find existing
		$existing = $client->video->v1->rooms->read(['uniqueName' => $roomName], 1);
		if (count($existing) > 0) {
			return $existing[0];
		}

		$opts = array_merge([
			'uniqueName' => $roomName,
			'type' => 'group',
			'recordParticipantsOnConnect' => false,
		], $options);

		return $client->video->v1->rooms->create($opts);
	}

	/**
	 * Generate a stable identity for a user or accept a string identity.
	 */
	public function generateIdentity($user): string
	{
		if (is_string($user)) return $user;
		if (is_object($user) && isset($user->id)) {
			return 'user-' . $user->id;
		}
		try {
			return 'guest-' . bin2hex(random_bytes(4));
		} catch (\Exception $e) {
			return 'guest-' . uniqid();
		}
	}

	/**
	 * Create an Access Token (JWT) for the given identity and room.
	 * Returns the token string.
	 */
	public function createAccessToken(string $identity, string $roomName, int $ttl = 3600): string
	{
		if (! $this->accountSid || ! $this->apiKeySid || ! $this->apiKeySecret) {
			throw new \RuntimeException('Twilio credentials not configured');
		}

		$token = new AccessToken($this->accountSid, $this->apiKeySid, $this->apiKeySecret, $ttl, $identity);
		$videoGrant = new VideoGrant();
		$videoGrant->setRoom($roomName);
		$token->addGrant($videoGrant);
		return $token->toJWT();
	}
}
