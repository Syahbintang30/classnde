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
        Schema::table('lessons', function (Blueprint $table) {
            if (!Schema::hasColumn('lessons', 'headline')) {
                $table->string('headline')->nullable()->after('title');
            }
            if (!Schema::hasColumn('lessons', 'sub_headline')) {
                $table->string('sub_headline')->nullable()->after('headline');
            }
            if (!Schema::hasColumn('lessons', 'youtube_link')) {
                $table->string('youtube_link')->nullable()->after('sub_headline');
            }
            if (!Schema::hasColumn('lessons', 'position')) {
                $table->integer('position')->default(0)->after('youtube_link');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (Schema::hasColumn('lessons', 'position')) {
                $table->dropColumn('position');
            }
            if (Schema::hasColumn('lessons', 'youtube_link')) {
                $table->dropColumn('youtube_link');
            }
            if (Schema::hasColumn('lessons', 'sub_headline')) {
                $table->dropColumn('sub_headline');
            }
            if (Schema::hasColumn('lessons', 'headline')) {
                $table->dropColumn('headline');
            }
        });
    }
};
