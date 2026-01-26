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
        Schema::table('waf_events', function (Blueprint $table) {
            // Composite index for tenant filtering with date range queries
            // Used in: WHERE site_id IN (...) AND event_time BETWEEN ...
            if (!$this->indexExists('waf_events', 'idx_site_event_time')) {
                $table->index(['site_id', 'event_time'], 'idx_site_event_time');
            }
            
            // Composite index for date range queries with status filtering
            // Used in: WHERE event_time BETWEEN ... AND status = ...
            if (!$this->indexExists('waf_events', 'idx_event_time_status')) {
                $table->index(['event_time', 'status'], 'idx_event_time_status');
            }
            
            // Composite index for site and status filtering
            // Used in: WHERE site_id IN (...) AND status = ...
            if (!$this->indexExists('waf_events', 'idx_site_status')) {
                $table->index(['site_id', 'status'], 'idx_site_status');
            }
            
            // Index on host for faster host-based filtering
            // Used in: WHERE host = ... or WHERE host LIKE ...
            if (!$this->indexExists('waf_events', 'idx_host')) {
                $table->index('host', 'idx_host');
            }
            
            // Index on site_id if not already exists (for tenant filtering)
            if (!$this->indexExists('waf_events', 'waf_events_site_id_index') && 
                !$this->indexExists('waf_events', 'idx_site_id')) {
                $table->index('site_id', 'idx_site_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('waf_events', function (Blueprint $table) {
            $table->dropIndex('idx_site_event_time');
            $table->dropIndex('idx_event_time_status');
            $table->dropIndex('idx_site_status');
            $table->dropIndex('idx_host');
            
            // Only drop site_id index if we created it
            if ($this->indexExists('waf_events', 'waf_events_site_id_index')) {
                $table->dropIndex('idx_site_id');
            }
        });
    }
    
    /**
     * Check if an index exists
     */
    private function indexExists(string $table, string $index): bool
    {
        try {
            $connection = Schema::getConnection();
            $driver = $connection->getDriverName();
            
            if ($driver === 'sqlite') {
                $indexes = $connection->select(
                    "SELECT name FROM sqlite_master WHERE type='index' AND tbl_name=? AND name=?",
                    [$table, $index]
                );
                return count($indexes) > 0;
            }
            
            // MySQL/MariaDB
            $databaseName = $connection->getDatabaseName();
            $result = $connection->select(
                "SELECT COUNT(*) as count FROM information_schema.statistics 
                 WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                [$databaseName, $table, $index]
            );
            
            return isset($result[0]) && $result[0]->count > 0;
        } catch (\Exception $e) {
            // If we can't check, assume it doesn't exist
            return false;
        }
    }
};
