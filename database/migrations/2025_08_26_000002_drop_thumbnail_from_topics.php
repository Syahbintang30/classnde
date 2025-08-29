<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropThumbnailFromTopics extends Migration
{
    /**
     * Run the migrations.
     *
     * Safely drop the `thumbnail` column if it exists.
     */
    public function up()
    {
        if (Schema::hasColumn('topics', 'thumbnail')) {
            Schema::table('topics', function (Blueprint $table) {
                $table->dropColumn('thumbnail');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * Recreate the `thumbnail` column (nullable string) after `description`.
     */
    public function down()
    {
        if (! Schema::hasColumn('topics', 'thumbnail')) {
            Schema::table('topics', function (Blueprint $table) {
                $table->string('thumbnail')->nullable()->after('description');
            });
        }
    }
}
