<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('referral_redemptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // the referrer user
            $table->unsignedInteger('units'); // number of 25% units redeemed this order
            $table->string('order_id')->nullable(); // link to order id if available
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_redemptions');
    }
};
