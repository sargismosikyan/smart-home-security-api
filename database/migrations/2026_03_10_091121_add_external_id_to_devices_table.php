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
        if (Schema::hasColumn('devices', 'external_id')) {
            return;
        }
        Schema::table('devices', function (Blueprint $table) {
            $table->string('external_id')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('devices', 'external_id')) {
            return;
        }
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('external_id');
        });
    }
};
