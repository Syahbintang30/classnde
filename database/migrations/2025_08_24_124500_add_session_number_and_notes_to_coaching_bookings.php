<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('coaching_bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('coaching_bookings', 'session_number')) {
                $table->integer('session_number')->default(1)->after('ticket_id');
            }
            if (! Schema::hasColumn('coaching_bookings', 'notes')) {
                $table->text('notes')->nullable()->after('session_number');
            }
        });
    }

    public function down()
    {
        Schema::table('coaching_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('coaching_bookings', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('coaching_bookings', 'session_number')) {
                $table->dropColumn('session_number');
            }
        });
    }
};
