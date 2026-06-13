<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_policies', function (Blueprint $table) {
            if (!Schema::hasColumn('site_policies', 'cache_enabled')) {
                // الكاش (proxy_cache) اختياري لكل موقع — افتراضياً معطّل
                $table->boolean('cache_enabled')->default(false)->after('rate_limit_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_policies', function (Blueprint $table) {
            if (Schema::hasColumn('site_policies', 'cache_enabled')) {
                $table->dropColumn('cache_enabled');
            }
        });
    }
};
