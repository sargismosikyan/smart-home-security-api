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
        if (Schema::hasColumn('security_alerts', 'device_id')) {
            return;
        }
        Schema::table('security_alerts', function (Blueprint $table) {
            $table->foreignId('device_id')->nullable()->after('id')->constrained('devices')->cascadeOnDelete();
            $table->string('alert_type')->nullable()->after('device_id');
            $table->string('severity')->nullable()->after('alert_type');
            $table->text('description')->nullable()->after('severity');
            $table->json('metadata')->nullable()->after('description');
            $table->timestamp('resolved_at')->nullable()->after('metadata');

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
        if (! Schema::hasColumn('security_alerts', 'device_id')) {
            return;
        }
        Schema::table('security_alerts', function (Blueprint $table) {
            $table->dropForeign(['device_id']);
            $table->dropIndex(['device_id']);
            $table->dropIndex(['severity']);
            $table->dropIndex(['created_at']);
            $table->dropColumn(['device_id', 'alert_type', 'severity', 'description', 'metadata', 'resolved_at']);
        });
    }
};
