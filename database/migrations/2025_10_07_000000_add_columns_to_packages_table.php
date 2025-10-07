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
        Schema::table('packages', function (Blueprint $table) {
            if (!Schema::hasColumn('packages', 'description')) {
                $table->text('description')->nullable()->after('price');
            }
            if (!Schema::hasColumn('packages', 'benefits')) {
                $table->text('benefits')->nullable()->after('description');
            }
            if (!Schema::hasColumn('packages', 'image')) {
                $table->string('image')->nullable()->after('benefits');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            if (Schema::hasColumn('packages', 'image')) {
                $table->dropColumn('image');
            }
            if (Schema::hasColumn('packages', 'benefits')) {
                $table->dropColumn('benefits');
            }
            if (Schema::hasColumn('packages', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
