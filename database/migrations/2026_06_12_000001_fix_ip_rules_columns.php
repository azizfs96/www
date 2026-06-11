<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the ip/type columns that were lost because the original
     * create_ip_rules migration only created id + timestamps.
     */
    public function up(): void
    {
        Schema::table('ip_rules', function (Blueprint $table) {
            if (!Schema::hasColumn('ip_rules', 'ip')) {
                $table->string('ip')->after('id');
            }
            if (!Schema::hasColumn('ip_rules', 'type')) {
                // SQLite stores enums as strings; default keeps existing rows valid.
                $table->string('type')->default('block')->after('ip');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ip_rules', function (Blueprint $table) {
            if (Schema::hasColumn('ip_rules', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('ip_rules', 'ip')) {
                $table->dropColumn('ip');
            }
        });
    }
};
