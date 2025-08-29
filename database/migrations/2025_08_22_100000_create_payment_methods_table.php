<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('display_name');
            $table->text('account_details')->nullable();
            $table->string('logo_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // seed a few defaults (BCA, GoPay, QRIS)
        \Illuminate\Support\Facades\DB::table('payment_methods')->insert([
            [
                'name' => 'BCA',
                'display_name' => 'Transfer Bank BCA',
                'account_details' => null,
                'logo_url' => 'pictures/logo_bca.png',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'GoPay',
                'display_name' => 'GoPay E-wallet',
                'account_details' => null,
                'logo_url' => 'pictures/logo_gopay.png',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'QRIS',
                'display_name' => 'QRIS',
                'account_details' => null,
                'logo_url' => 'pictures/logo_qris.png',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
