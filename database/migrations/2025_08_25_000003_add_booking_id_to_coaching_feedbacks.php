<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('coaching_feedbacks', 'booking_id')) {
            Schema::table('coaching_feedbacks', function (Blueprint $table) {
                $table->unsignedBigInteger('booking_id')->nullable()->after('user_id')->index();
            });
        }
    }

    public function down()
    {
        Schema::table('coaching_feedbacks', function (Blueprint $table) {
            $table->dropColumn('booking_id');
        });
    }
};
