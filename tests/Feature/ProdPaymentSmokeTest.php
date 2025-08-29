<?php
namespace Tests\Feature;

use Tests\TestCase;

class ProdPaymentSmokeTest extends TestCase
{
    /**
     * This test attempts a minimal Midtrans Snap transaction against production.
     * It will be skipped unless RUN_PROD_PAYMENT_TEST=1 and MIDTRANS_SERVER_KEY is provided.
     * Run locally only and do NOT enable on CI.
     *
     * Environment variables used:
     * - RUN_PROD_PAYMENT_TEST=1
     * - MIDTRANS_SERVER_KEY=your_production_server_key
     * - AMOUNT (optional, default 1000)
     */
    public function test_create_snap_transaction_in_production()
    {
        if (getenv('RUN_PROD_PAYMENT_TEST') !== '1') {
            $this->markTestSkipped('Production payment smoke tests are disabled. Set RUN_PROD_PAYMENT_TEST=1 to enable.');
        }

        $serverKey = getenv('MIDTRANS_SERVER_KEY');
        $this->assertNotEmpty($serverKey, 'MIDTRANS_SERVER_KEY must be set for this test');

        $amount = getenv('AMOUNT') ?: 1000;
        $orderId = 'phpunit-smoke-' . uniqid();

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int)$amount,
            ],
            'item_details' => [
                [
                    'id' => 'phpunit-smoke',
                    'price' => (int)$amount,
                    'quantity' => 1,
                    'name' => 'PHPUnit Smoke Item',
                ],
            ],
            'customer_details' => [
                'first_name' => 'PHPUnit',
                'last_name' => 'Smoke',
                'email' => 'phpunit-smoke@example.invalid',
            ],
        ];

        $ch = curl_init('https://app.midtrans.com/snap/v1/transactions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $auth = base64_encode($serverKey . ':');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . $auth,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        $this->assertEmpty($err, 'cURL error occurred: ' . $err);
        $this->assertTrue(in_array($httpCode, [200, 201]), 'Expected HTTP 200/201; got ' . $httpCode . '. Response: ' . $response);

        // Response should contain a token or redirect_url for Snap
        $this->assertTrue(
            (strpos($response, 'token') !== false) || (strpos($response, 'redirect_url') !== false),
            'Response does not look like a valid Snap response: ' . $response
        );
    }
}
