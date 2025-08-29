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
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('referrer_user_id')->nullable()->after('package_id');
            $table->string('referral_code')->nullable()->after('referrer_user_id');
            $table->integer('original_amount')->nullable()->after('amount')->comment('Amount before any referral discount');
            $table->foreign('referrer_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['referrer_user_id']);
            $table->dropColumn(['referrer_user_id','referral_code','original_amount']);
        });
    }
};
