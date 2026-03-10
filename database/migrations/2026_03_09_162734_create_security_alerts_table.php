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
        Schema::create('security_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->cascadeOnDelete();
            $table->string('alert_type');
            $table->string('severity');
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('device_id');
            $table->index('severity');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_alerts');
    }
};
