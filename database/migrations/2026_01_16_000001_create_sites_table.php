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
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم الموقع للعرض
            $table->string('server_name'); // النطاق (domain) - مثل: rabbitclean.sa
            $table->string('backend_ip'); // IP السيرفر الخلفي
            $table->integer('backend_port')->default(80); // بورت السيرفر الخلفي
            $table->boolean('ssl_enabled')->default(false); // هل SSL مفعل؟
            $table->string('ssl_cert_path')->nullable(); // مسار شهادة SSL
            $table->string('ssl_key_path')->nullable(); // مسار مفتاح SSL
            $table->boolean('enabled')->default(true); // هل الموقع مفعل؟
            $table->text('notes')->nullable(); // ملاحظات
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
