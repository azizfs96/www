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
    Schema::create('waf_events', function (Blueprint $table) {
        $table->id();
        $table->timestamp('event_time')->index();
        $table->string('client_ip')->index();
        $table->string('host')->nullable();
        $table->string('uri', 2000)->nullable();
        $table->string('method', 10)->nullable();
        $table->integer('status')->nullable();
        $table->string('rule_id')->nullable()->index();
        $table->string('severity')->nullable();
        $table->string('message', 500)->nullable();
        $table->string('action')->nullable(); // لاحقًا لو احتجناها
        $table->string('user_agent', 500)->nullable();
        $table->string('unique_id')->unique(); // من ModSecurity
        $table->json('raw')->nullable();       // الحدث كامل
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waf_events');
    }
};
