<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('url_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();        // اسم وصفي: Admin Panel
            $table->string('path');                    // مثل: /admin
            $table->text('allowed_ips');               // 99.6.12.1, 1.2.3.4 ...
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('url_rules');
    }
};
