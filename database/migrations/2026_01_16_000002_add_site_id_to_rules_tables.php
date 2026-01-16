<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * إضافة site_id للجداول لدعم القواعد على مستوى الموقع
     * null = قاعدة عامة تُطبق على كل المواقع
     * site_id = قاعدة خاصة بموقع معين فقط
     */
    public function up(): void
    {
        // إضافة site_id لجدول IP Rules
        Schema::table('ip_rules', function (Blueprint $table) {
            $table->foreignId('site_id')
                ->nullable()
                ->after('id')
                ->constrained('sites')
                ->onDelete('cascade');
            
            $table->index(['site_id', 'type']);
        });

        // إضافة site_id لجدول URL Rules
        Schema::table('url_rules', function (Blueprint $table) {
            $table->foreignId('site_id')
                ->nullable()
                ->after('id')
                ->constrained('sites')
                ->onDelete('cascade');
            
            $table->index('site_id');
        });

        // إضافة site_id لجدول Country Rules
        Schema::table('country_rules', function (Blueprint $table) {
            $table->foreignId('site_id')
                ->nullable()
                ->after('id')
                ->constrained('sites')
                ->onDelete('cascade');
            
            $table->index(['site_id', 'type']);
        });

        // إضافة site_id لجدول WAF Events (للربط)
        Schema::table('waf_events', function (Blueprint $table) {
            $table->foreignId('site_id')
                ->nullable()
                ->after('id')
                ->constrained('sites')
                ->onDelete('set null');
            
            $table->index('site_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ip_rules', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->dropColumn('site_id');
        });

        Schema::table('url_rules', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->dropColumn('site_id');
        });

        Schema::table('country_rules', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->dropColumn('site_id');
        });

        Schema::table('waf_events', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->dropColumn('site_id');
        });
    }
};
