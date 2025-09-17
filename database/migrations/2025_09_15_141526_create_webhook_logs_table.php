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
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('order_uuid');
            $table->string('txn_id');
            $table->json('raw_payload');
            $table->enum('status', ['pending', 'processed', 'failed', 'ignored'])->default('pending');
            $table->integer('attempts')->default(1);
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
