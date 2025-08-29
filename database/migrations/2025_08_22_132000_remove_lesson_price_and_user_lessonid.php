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
        // remove price column from lessons if present
        if (Schema::hasColumn('lessons', 'price')) {
            Schema::table('lessons', function (Blueprint $table) {
                $table->dropColumn('price');
            });
        }

        // remove lesson_id FK and column from users if present
        if (Schema::hasColumn('users', 'lesson_id')) {
            Schema::table('users', function (Blueprint $table) {
                // drop foreign if exists
                try {
                    $table->dropForeign(['lesson_id']);
                } catch (\Exception $e) {
                    // ignore if constraint not present
                }
                $table->dropColumn('lesson_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (! Schema::hasColumn('lessons', 'price')) {
                $table->unsignedInteger('price')->default(125000)->after('position');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'lesson_id')) {
                $table->unsignedBigInteger('lesson_id')->nullable()->after('phone');
                $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('set null');
            }
        });
    }
};
