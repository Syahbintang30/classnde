<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('coaching_tickets', function (Blueprint $table) {
            $table->timestamp('used_at')->nullable()->after('is_used');
            $table->unsignedBigInteger('used_by_admin_id')->nullable()->after('used_at');
            $table->foreign('used_by_admin_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('coaching_tickets', function (Blueprint $table) {
            $table->dropForeign(['used_by_admin_id']);
            $table->dropColumn(['used_at', 'used_by_admin_id']);
        });
    }
};
