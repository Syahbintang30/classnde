<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('coaching_slot_capacities', function (Blueprint $table) {
            if (Schema::hasColumn('coaching_slot_capacities', 'capacity')) {
                $table->dropColumn('capacity');
            }
        });
    }

    public function down()
    {
        Schema::table('coaching_slot_capacities', function (Blueprint $table) {
            if (! Schema::hasColumn('coaching_slot_capacities', 'capacity')) {
                $table->integer('capacity')->default(1)->after('time');
            }
        });
    }
};
