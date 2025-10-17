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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('actor_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('target_user_id')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->text('action');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
