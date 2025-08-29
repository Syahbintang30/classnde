<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Add notes column to coaching_bookings if missing
        if (!Schema::hasColumn('coaching_bookings', 'notes')) {
            Schema::table('coaching_bookings', function (Blueprint $table) {
                $table->text('notes')->nullable()->after('session_number');
            });
        }

        // Migrate coaching_feedbacks into coaching_bookings.notes
        if (Schema::hasTable('coaching_feedbacks')) {
            DB::transaction(function () {
                $feedbacks = DB::table('coaching_feedbacks')->get();
                foreach ($feedbacks as $f) {
                    // Append keluh_kesah and want_to_learn into notes column
                    $parts = [];
                    if (!empty($f->keluh_kesah)) $parts[] = "Keluhan: " . $f->keluh_kesah;
                    if (!empty($f->want_to_learn)) $parts[] = "Ingin belajar: " . $f->want_to_learn;

                    if (!empty($parts)) {
                        $note = implode("\n\n", $parts);
                        // If coaching_bookings has existing notes, preserve them
                        $existing = DB::table('coaching_bookings')->where('id', $f->booking_id)->value('notes');
                        if ($existing) {
                            $note = $existing . "\n\n" . $note;
                        }
                        DB::table('coaching_bookings')->where('id', $f->booking_id)->update(['notes' => $note]);
                    }
                }
            });

            // drop feedbacks table
            Schema::dropIfExists('coaching_feedbacks');
        }

        // Migrate coaching_events: append brief event summary into notes (per booking)
        if (Schema::hasTable('coaching_events')) {
            DB::transaction(function () {
                $events = DB::table('coaching_events')->orderBy('created_at')->get();
                foreach ($events as $e) {
                    $meta = null;
                    try { $meta = json_encode(json_decode($e->meta)); } catch (\Throwable $ex) { $meta = $e->meta; }
                    $line = "Event ({$e->event}) by user {$e->user_id} at {$e->created_at}" . (!empty($meta) ? " meta: " . $meta : '');
                    $existing = DB::table('coaching_bookings')->where('id', $e->booking_id)->value('notes');
                    $note = $existing ? $existing . "\n\n" . $line : $line;
                    DB::table('coaching_bookings')->where('id', $e->booking_id)->update(['notes' => $note]);
                }
            });

            Schema::dropIfExists('coaching_events');
        }

        // Migrate caching_bookings: if there is meta or time/date info that is useful, append it
        if (Schema::hasTable('caching_bookings')) {
            DB::transaction(function () {
                $caches = DB::table('caching_bookings')->get();
                foreach ($caches as $c) {
                    $parts = [];
                    if (!empty($c->date)) $parts[] = "Cached date: " . $c->date;
                    if (!empty($c->time)) $parts[] = "Cached time: " . $c->time;
                    if (!empty($c->meta)) {
                        try { $m = json_encode(json_decode($c->meta)); } catch (\Throwable $ex) { $m = $c->meta; }
                        $parts[] = "Meta: " . $m;
                    }
                    if (!empty($parts)) {
                        $line = implode("\n", $parts);
                        $existing = DB::table('coaching_bookings')->where('id', $c->booking_id)->value('notes');
                        $note = $existing ? $existing . "\n\n" . $line : $line;
                        DB::table('coaching_bookings')->where('id', $c->booking_id)->update(['notes' => $note]);
                    }
                }
            });

            Schema::dropIfExists('caching_bookings');
        }
    }

    public function down()
    {
        // Best-effort rollback: recreate dropped tables with minimal schema. Data cannot be reconstructed perfectly.
        if (!Schema::hasTable('coaching_feedbacks')) {
            Schema::create('coaching_feedbacks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('booking_id')->constrained('coaching_bookings')->onDelete('cascade');
                $table->text('keluh_kesah')->nullable();
                $table->text('want_to_learn')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('coaching_events')) {
            Schema::create('coaching_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('booking_id')->constrained('coaching_bookings')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('event');
                $table->text('meta')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('caching_bookings')) {
            Schema::create('caching_bookings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('booking_id')->nullable()->constrained('coaching_bookings')->nullOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->date('date')->nullable();
                $table->time('time')->nullable();
                $table->string('status')->nullable();
                $table->text('meta')->nullable();
                $table->timestamps();
            });
        }

        // Remove notes column from coaching_bookings if present
        if (Schema::hasColumn('coaching_bookings', 'notes')) {
            Schema::table('coaching_bookings', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }
    }
};
