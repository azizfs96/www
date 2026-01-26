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
        Schema::create('site_backend_servers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->string('ip'); // IP السيرفر الخلفي
            $table->integer('port')->default(80); // بورت السيرفر الخلفي
            $table->enum('status', ['active', 'standby'])->default('standby'); // الحالة: active أو standby
            $table->integer('priority')->default(1); // الأولوية (1 = أعلى أولوية)
            $table->boolean('health_check_enabled')->default(true); // تفعيل فحص الصحة
            $table->timestamp('last_health_check')->nullable(); // آخر فحص صحة
            $table->boolean('is_healthy')->default(true); // هل السيرفر صحي؟
            $table->integer('fail_count')->default(0); // عدد مرات الفشل المتتالية
            $table->timestamps();
            
            // فهرس للأداء
            $table->index(['site_id', 'status']);
            $table->index(['site_id', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_backend_servers');
    }
};
