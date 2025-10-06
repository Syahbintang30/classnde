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
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->index();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, array, json
            $table->string('category')->default('general'); // validation, security, rate_limiting, etc
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false); // Can be accessed by frontend
            $table->boolean('requires_restart')->default(false); // Requires app restart to take effect
            $table->json('validation_rules')->nullable(); // JSON validation rules
            $table->string('updated_by')->nullable(); // Admin who last updated
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['category', 'key']);
            $table->index('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
