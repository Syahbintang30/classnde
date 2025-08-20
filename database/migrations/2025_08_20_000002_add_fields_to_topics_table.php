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
        Schema::table('topics', function (Blueprint $table) {
            if (!Schema::hasColumn('topics', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (!Schema::hasColumn('topics', 'position')) {
                $table->integer('position')->default(0)->after('description');
            }
            if (!Schema::hasColumn('topics', 'video_url')) {
                $table->string('video_url')->nullable()->after('position');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            if (Schema::hasColumn('topics', 'video_url')) {
                $table->dropColumn('video_url');
            }
            if (Schema::hasColumn('topics', 'position')) {
                $table->dropColumn('position');
            }
            if (Schema::hasColumn('topics', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
