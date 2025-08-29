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
        Schema::table('coaching_bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('coaching_bookings', 'admin_note')) {
                $table->text('admin_note')->nullable()->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coaching_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('coaching_bookings', 'admin_note')) {
                $table->dropColumn('admin_note');
            }
        });
    }
};
