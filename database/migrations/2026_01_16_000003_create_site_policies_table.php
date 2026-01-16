<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * جدول إعدادات WAF لكل موقع
     */
    public function up(): void
    {
        Schema::create('site_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')
                ->constrained('sites')
                ->onDelete('cascade');
            
            // إعدادات ModSecurity
            $table->boolean('waf_enabled')->default(true);
            $table->integer('paranoia_level')->default(1); // 1-4 (1=أقل صرامة، 4=أكثر صرامة)
            $table->string('anomaly_threshold')->default('5'); // عتبة الشذوذ
            
            // إعدادات القواعد
            $table->boolean('inherit_global_rules')->default(true); // وراثة القواعد العامة
            $table->boolean('block_suspicious_user_agents')->default(true);
            $table->boolean('block_sql_injection')->default(true);
            $table->boolean('block_xss')->default(true);
            $table->boolean('block_rce')->default(true); // Remote Code Execution
            $table->boolean('block_lfi')->default(true); // Local File Inclusion
            $table->boolean('block_rfi')->default(true); // Remote File Inclusion
            
            // Rate Limiting
            $table->boolean('rate_limiting_enabled')->default(false);
            $table->integer('requests_per_minute')->nullable();
            $table->integer('burst_size')->nullable();
            
            // استثناءات
            $table->text('excluded_urls')->nullable(); // URLs مستثناة من WAF (سطر لكل URL)
            $table->text('excluded_ips')->nullable(); // IPs مستثناة (سطر لكل IP)
            
            // Logging
            $table->boolean('detailed_logging')->default(false);
            $table->string('log_level')->default('warn'); // debug, info, warn, error
            
            // Custom Rules
            $table->text('custom_modsec_rules')->nullable(); // قواعد ModSecurity مخصصة
            
            // ملاحظات
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // فهرس فريد: موقع واحد = سياسة واحدة
            $table->unique('site_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_policies');
    }
};
