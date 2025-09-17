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
        // Add indexes to orders table for better performance
        Schema::table('orders', function (Blueprint $table) {
            // Index for user_id lookups
            $table->index('user_id', 'idx_orders_user_id');
            
            // Index for status filtering
            $table->index('status', 'idx_orders_status');
            
            // Index for created_at for date range queries
            $table->index('created_at', 'idx_orders_created_at');
            
            // Index for updated_at for recent updates
            $table->index('updated_at', 'idx_orders_updated_at');
            
            // CRITICAL: Index for external_txn_id (webhook processing)
            $table->index('external_txn_id', 'idx_orders_external_txn_id');
            
            // CRITICAL: Index for amount-based queries
            $table->index('amount', 'idx_orders_amount');
            
            // Composite index for user_id + status (common query pattern)
            $table->index(['user_id', 'status'], 'idx_orders_user_status');
            
            // Composite index for status + created_at (for reporting)
            $table->index(['status', 'created_at'], 'idx_orders_status_created');
            
            // CRITICAL: Composite index for webhook processing (status + external_txn_id)
            $table->index(['status', 'external_txn_id'], 'idx_orders_status_external_txn');
            
            // CRITICAL: Composite index for user analytics (user_id + created_at)
            $table->index(['user_id', 'created_at'], 'idx_orders_user_created');
        });

        // Add indexes to order_events table
        Schema::table('order_events', function (Blueprint $table) {
            // Index for order_id lookups
            $table->index('order_id', 'idx_order_events_order_id');
            
            // Index for to_status filtering
            $table->index('to_status', 'idx_order_events_to_status');
            
            // Index for created_at for chronological queries
            $table->index('created_at', 'idx_order_events_created_at');
            
            // Composite index for order_id + created_at (for chronological order events)
            $table->index(['order_id', 'created_at'], 'idx_order_events_order_created');
        });

        // Add indexes to webhook_logs table
        Schema::table('webhook_logs', function (Blueprint $table) {
            // Index for order_uuid lookups
            $table->index('order_uuid', 'idx_webhook_logs_order_uuid');
            
            // Index for txn_id lookups
            $table->index('txn_id', 'idx_webhook_logs_txn_id');
            
            // Index for status filtering
            $table->index('status', 'idx_webhook_logs_status');
            
            // Index for created_at for date range queries
            $table->index('created_at', 'idx_webhook_logs_created_at');
            
            // Note: processed_at index will be added in later migration (2025_09_16_220447_enhance_webhook_logs_for_scale.php)
            
            // Composite index for status + created_at (for monitoring)
            $table->index(['status', 'created_at'], 'idx_webhook_logs_status_created');
            
            // CRITICAL: Composite index for webhook monitoring (order_uuid + status)
            $table->index(['order_uuid', 'status'], 'idx_webhook_logs_order_status');
            
            // CRITICAL: Composite index for webhook processing (txn_id + status)
            $table->index(['txn_id', 'status'], 'idx_webhook_logs_txn_status');
        });

        // Add indexes to users table
        Schema::table('users', function (Blueprint $table) {
            // Index for email lookups (already exists as unique, but adding for completeness)
            $table->index('email', 'idx_users_email');
            
            // Index for created_at for user analytics
            $table->index('created_at', 'idx_users_created_at');
        });

        // CRITICAL: Add indexes to jobs table for queue performance
        Schema::table('jobs', function (Blueprint $table) {
            // CRITICAL: Composite index for queue processing (queue + available_at)
            $table->index(['queue', 'available_at'], 'idx_jobs_queue_available');
        });

        // CRITICAL: Add indexes to failed_jobs table for failed job analysis
        Schema::table('failed_jobs', function (Blueprint $table) {
            // CRITICAL: Index for failed jobs analysis
            $table->index('failed_at', 'idx_failed_jobs_failed_at');
            
            // CRITICAL: Index for queue analysis
            $table->index('queue', 'idx_failed_jobs_queue');
            
            // CRITICAL: Composite index for queue + failed_at analysis
            $table->index(['queue', 'failed_at'], 'idx_failed_jobs_queue_failed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes from orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_user_id');
            $table->dropIndex('idx_orders_status');
            $table->dropIndex('idx_orders_created_at');
            $table->dropIndex('idx_orders_updated_at');
            $table->dropIndex('idx_orders_external_txn_id');
            $table->dropIndex('idx_orders_amount');
            $table->dropIndex('idx_orders_user_status');
            $table->dropIndex('idx_orders_status_created');
            $table->dropIndex('idx_orders_status_external_txn');
            $table->dropIndex('idx_orders_user_created');
        });

        // Drop indexes from order_events table
        Schema::table('order_events', function (Blueprint $table) {
            $table->dropIndex('idx_order_events_order_id');
            $table->dropIndex('idx_order_events_to_status');
            $table->dropIndex('idx_order_events_created_at');
            $table->dropIndex('idx_order_events_order_created');
        });

        // Drop indexes from webhook_logs table
        Schema::table('webhook_logs', function (Blueprint $table) {
            $table->dropIndex('idx_webhook_logs_order_uuid');
            $table->dropIndex('idx_webhook_logs_txn_id');
            $table->dropIndex('idx_webhook_logs_status');
            $table->dropIndex('idx_webhook_logs_created_at');
            // Note: processed_at index is handled in later migration
            $table->dropIndex('idx_webhook_logs_status_created');
            $table->dropIndex('idx_webhook_logs_order_status');
            $table->dropIndex('idx_webhook_logs_txn_status');
        });

        // Drop indexes from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_email');
            $table->dropIndex('idx_users_created_at');
        });

        // Drop indexes from jobs table
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropIndex('idx_jobs_queue_available');
        });

        // Drop indexes from failed_jobs table
        Schema::table('failed_jobs', function (Blueprint $table) {
            $table->dropIndex('idx_failed_jobs_failed_at');
            $table->dropIndex('idx_failed_jobs_queue');
            $table->dropIndex('idx_failed_jobs_queue_failed');
        });
    }
};