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
        Schema::table('site_policies', function (Blueprint $table) {
            $table->string('custom_403_page_path')->nullable()->after('notes');
            $table->text('custom_403_message')->nullable()->after('custom_403_page_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_policies', function (Blueprint $table) {
            $table->dropColumn(['custom_403_page_path', 'custom_403_message']);
        });
    }
};
