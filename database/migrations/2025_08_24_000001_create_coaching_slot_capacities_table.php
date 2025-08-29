<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('coaching_slot_capacities', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('time',10);
            $table->integer('capacity')->default(1);
            $table->timestamps();
            $table->unique(['date','time']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('coaching_slot_capacities');
    }
};
