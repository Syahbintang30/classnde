<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropTopicProgressesTable extends Migration
{
    /**
     * Run the migrations.
     * Drops the `topic_progresses` table if it exists.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('topic_progresses')) {
            Schema::drop('topic_progresses');
        }
    }

    /**
     * Reverse the migrations.
     * Recreates the `topic_progresses` table with a minimal schema.
     * Note: this recreates a simple version of the original table (id, user_id, topic_id, watched_seconds, completed, timestamps).
     * If your original migration had foreign keys or a different schema, adjust accordingly.
     *
     * @return void
     */
    public function down()
    {
        if (! Schema::hasTable('topic_progresses')) {
            Schema::create('topic_progresses', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('topic_id')->index();
                $table->integer('watched_seconds')->default(0);
                $table->boolean('completed')->default(false);
                $table->timestamps();
            });
        }
    }
}
