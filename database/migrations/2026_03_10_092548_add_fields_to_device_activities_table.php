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
        if (Schema::hasColumn('device_activities', 'device_id')) {
            return;
        }
        Schema::table('device_activities', function (Blueprint $table) {
            $table->foreignId('device_id')->nullable()->after('id');
            $table->string('event_type')->nullable()->after('device_id');
            $table->json('payload')->nullable()->after('event_type');
            $table->timestamp('occurred_at')->nullable()->after('payload');
            $table->string('ip_address')->nullable()->after('occurred_at');

            $table->index(['device_id', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('device_activities', 'device_id')) {
            return;
        }
        Schema::table('device_activities', function (Blueprint $table) {
            $table->dropIndex(['device_id', 'occurred_at']);
            $table->dropColumn(['device_id', 'event_type', 'payload', 'occurred_at', 'ip_address']);
        });
    }
};
