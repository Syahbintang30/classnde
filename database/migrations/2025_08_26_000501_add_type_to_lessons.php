<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('lessons', function (Blueprint $table) {
            // Add a simple 'type' column; avoid using ->after() because some installations may not have a 'price' column.
            $table->string('type')->nullable()->default('course')->index();
        });
    }

    public function down()
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (Schema::hasColumn('lessons', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
