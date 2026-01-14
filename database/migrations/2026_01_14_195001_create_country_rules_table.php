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
        Schema::create('country_rules', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 2); // Country code (e.g., 'US', 'SA', 'CN')
            $table->enum('type', ['allow', 'block']); // allow or block
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            
            $table->unique(['country_code', 'type']); // Prevent duplicates
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_rules');
    }
};
