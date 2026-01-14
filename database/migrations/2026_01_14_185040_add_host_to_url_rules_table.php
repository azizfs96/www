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
        Schema::table('url_rules', function (Blueprint $table) {
            $table->string('host')->nullable()->after('path'); // مثل: rabbitclean.sa
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('url_rules', function (Blueprint $table) {
            $table->dropColumn('host');
        });
    }
};
