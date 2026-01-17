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
            // Additional OWASP CRS Attack Types
            $table->boolean('block_path_traversal')->default(true)->after('block_rfi');
            $table->boolean('block_php_injection')->default(true)->after('block_path_traversal');
            $table->boolean('block_java_injection')->default(true)->after('block_php_injection');
            $table->boolean('block_session_fixation')->default(true)->after('block_java_injection');
            $table->boolean('block_file_upload_attacks')->default(true)->after('block_session_fixation');
            $table->boolean('block_scanner_detection')->default(true)->after('block_file_upload_attacks');
            $table->boolean('block_protocol_attacks')->default(true)->after('block_scanner_detection');
            $table->boolean('block_dos_protection')->default(false)->after('block_protocol_attacks');
            $table->boolean('block_data_leakages')->default(true)->after('block_dos_protection');
            $table->boolean('block_nodejs_injection')->default(true)->after('block_data_leakages');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_policies', function (Blueprint $table) {
            $table->dropColumn([
                'block_path_traversal',
                'block_php_injection',
                'block_java_injection',
                'block_session_fixation',
                'block_file_upload_attacks',
                'block_scanner_detection',
                'block_protocol_attacks',
                'block_dos_protection',
                'block_data_leakages',
                'block_nodejs_injection',
            ]);
        });
    }
};
