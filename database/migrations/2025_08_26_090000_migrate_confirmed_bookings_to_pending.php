<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('coaching_bookings')) {
            // update legacy 'confirmed' statuses to the new 'pending' default
            DB::table('coaching_bookings')->where('status', 'confirmed')->update(['status' => 'pending']);
        }
    }

    public function down()
    {
        if (Schema::hasTable('coaching_bookings')) {
            // best-effort rollback: revert 'pending' values that were originally 'confirmed'
            // Note: can't reliably detect which pending rows came from confirmed; skip revert.
        }
    }
};
