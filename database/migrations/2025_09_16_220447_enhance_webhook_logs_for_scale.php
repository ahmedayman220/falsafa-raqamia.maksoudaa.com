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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
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