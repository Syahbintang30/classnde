<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (Schema::hasColumn('lessons', 'youtube_link')) {
                $table->dropColumn('youtube_link');
            }
            if (Schema::hasColumn('lessons', 'headline')) {
                $table->dropColumn('headline');
            }
            if (Schema::hasColumn('lessons', 'sub_headline')) {
                $table->dropColumn('sub_headline');
            }
            if (Schema::hasColumn('lessons', 'description')) {
                $table->dropColumn('description');
            }
        });
    }

    public function down()
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (! Schema::hasColumn('lessons', 'headline')) {
                $table->string('headline')->nullable();
            }
            if (! Schema::hasColumn('lessons', 'sub_headline')) {
                $table->string('sub_headline')->nullable();
            }
            if (! Schema::hasColumn('lessons', 'youtube_link')) {
                $table->string('youtube_link')->nullable();
            }
            if (! Schema::hasColumn('lessons', 'description')) {
                $table->text('description')->nullable();
            }
        });
    }
};
