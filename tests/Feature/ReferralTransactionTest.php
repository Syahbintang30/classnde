<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Package;
use App\Models\Transaction;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

class ReferralTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_transaction_with_referral_applies_discount_and_records_original_amount()
    {
        // create a package
        $pkg = Package::factory()->create(['price' => 100000]);

        // set referral discount percent
        Setting::set('referral.discount_percent', '5');

        // create referrer user
        $ref = User::factory()->create();

        // call createSnapToken endpoint (simulate client)
        $response = $this->postJson('/api/midtrans/create', [
            'package_id' => $pkg->id,
            'gross_amount' => 100000,
            'package_qty' => 1,
            'package_unit_price' => 100000,
            'payment_method' => 'qris',
            'referral' => $ref->referral_code,
        ]);

        $response->assertStatus(200);
        $body = $response->json();
        $this->assertArrayHasKey('order_id', $body);

        // transaction should exist with amount < original_amount and referral_code set
        $txn = Transaction::where('order_id', $body['order_id'])->first();
        $this->assertNotNull($txn);
        $this->assertEquals($txn->original_amount, 100000);
        $this->assertLessThan($txn->original_amount, $txn->amount);
        $this->assertEquals($txn->referral_code, $ref->referral_code);
        $this->assertEquals($txn->referrer_user_id, $ref->id);
    }
}
