<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('coaching_bookings', function (Blueprint $table) {
            // drop constrained foreign id if exists
            if (Schema::hasColumn('coaching_bookings', 'coach_user_id')) {
                // use dropConstrainedForeignId when available
                try {
                    $table->dropConstrainedForeignId('coach_user_id');
                } catch (\Throwable $e) {
                    // fallback: drop foreign and column manually
                    $sm = Schema::getConnection()->getDoctrineSchemaManager();
                    $sm->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
                    if (Schema::hasColumn('coaching_bookings', 'coach_user_id')) {
                        $table->dropColumn('coach_user_id');
                    }
                }
            }
        });
    }

    public function down()
    {
        Schema::table('coaching_bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('coaching_bookings', 'coach_user_id')) {
                $table->foreignId('coach_user_id')->nullable()->constrained('users')->nullOnDelete()->after('user_id');
            }
        });
    }
};
