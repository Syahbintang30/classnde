<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('transactions')) return;
        if (! Schema::hasColumn('transactions', 'lesson_id')) return;

        $connection = config('database.default');
        $dbName = config("database.connections.{$connection}.database");

        // find foreign key constraint name for transactions.lesson_id if exists
        $fk = DB::selectOne(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL LIMIT 1',
            [$dbName, 'transactions', 'lesson_id']
        );

        if ($fk && isset($fk->CONSTRAINT_NAME)) {
            try {
                DB::statement("ALTER TABLE `transactions` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            } catch (\Throwable $e) {
                // ignore if can't drop
            }
        }

        // drop any index on lesson_id (index name may vary)
        $idx = DB::selectOne(
            'SELECT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1',
            [$dbName, 'transactions', 'lesson_id']
        );
        if ($idx && isset($idx->INDEX_NAME)) {
            try { DB::statement("DROP INDEX `{$idx->INDEX_NAME}` ON `transactions`"); } catch (\Throwable $e) { /* ignore */ }
        }

        // now drop the column
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('lesson_id');
        });
    }

    public function down()
    {
        if (! Schema::hasColumn('transactions', 'lesson_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->unsignedBigInteger('lesson_id')->nullable()->after('user_id')->index();
                // no foreign key restored to avoid migration failures if lessons table differs
            });
        }
    }
};
