<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('coaching_feedbacks', function (Blueprint $table) {
            $table->text('admin_action')->nullable()->after('want_to_learn');
            $table->unsignedBigInteger('admin_user_id')->nullable()->after('admin_action');
        });
    }

    public function down()
    {
        Schema::table('coaching_feedbacks', function (Blueprint $table) {
            $table->dropColumn(['admin_action', 'admin_user_id']);
        });
    }
};
