<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * ENHANCED for massive scale webhook processing
     */
    public function up(): void
    {
        Schema::table('webhook_logs', function (Blueprint $table) {
            // CRITICAL: Add processed_at timestamp for performance monitoring
            $table->timestamp('processed_at')->nullable()->after('last_error');
            
            // CRITICAL: Add processing_time_ms for observability
            $table->integer('processing_time_ms')->nullable()->after('processed_at');
            
            // CRITICAL: Add retry_count for monitoring retry patterns
            $table->integer('retry_count')->default(0)->after('attempts');
            
            // CRITICAL: Add webhook_source for tracking different payment gateways
            $table->string('webhook_source')->nullable()->after('txn_id');
            
            // CRITICAL: Add correlation_id for distributed tracing
            $table->string('correlation_id')->nullable()->after('webhook_source');
        });

        // Add indexes for the new columns
        Schema::table('webhook_logs', function (Blueprint $table) {
            // CRITICAL: Index for processed_at (webhook processing time analysis)
            $table->index('processed_at', 'idx_webhook_logs_processed_at');
            
            // CRITICAL: Index for webhook_source (payment gateway analysis)
            $table->index('webhook_source', 'idx_webhook_logs_webhook_source');
            
            // CRITICAL: Index for correlation_id (distributed tracing)
            $table->index('correlation_id', 'idx_webhook_logs_correlation_id');
            
            // CRITICAL: Composite index for webhook_source + status (gateway monitoring)
            $table->index(['webhook_source', 'status'], 'idx_webhook_logs_source_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes first
        Schema::table('webhook_logs', function (Blueprint $table) {
            $table->dropIndex('idx_webhook_logs_processed_at');
            $table->dropIndex('idx_webhook_logs_webhook_source');
            $table->dropIndex('idx_webhook_logs_correlation_id');
            $table->dropIndex('idx_webhook_logs_source_status');
        });

        // Then drop columns
        Schema::table('webhook_logs', function (Blueprint $table) {
            $table->dropColumn([
                'processed_at',
                'processing_time_ms', 
                'retry_count',
                'webhook_source',
                'correlation_id'
            ]);
        });
    }
};