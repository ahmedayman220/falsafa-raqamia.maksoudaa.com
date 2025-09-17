<?php

namespace Tests\Feature;

use App\Jobs\ProcessPaymentWebhookJob;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\User;
use App\Models\WebhookLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test basic application response
     */
    public function test_application_returns_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /**
     * Test webhook changes order status from pending to paid and creates order event
     */
    public function test_webhook_changes_order_status_from_pending_to_paid(): void
    {
        // Create test user and order
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'amount' => 100.00,
            'status' => 'pending',
            'metadata' => ['product' => 'Test Product'],
        ]);

        // Simulate webhook request
        $webhookData = [
            'txn_id' => 'tx_test_123',
            'order_uuid' => $order->id,
            'status' => 'paid',
            'amount' => 100.00,
            'timestamp' => '2025-09-15T10:00:00Z',
            'metadata' => [
                'payment_method' => 'credit_card',
                'processor' => 'stripe',
            ],
        ];

        // Send webhook request
        $response = $this->postJson('/api/webhooks/payments', $webhookData);

        // Assert webhook was accepted
        $response->assertStatus(202)
                ->assertJson([
                    'message' => 'Webhook received and queued for processing',
                ]);

        $webhookLogId = $response->json('webhook_log_id');

        // Assert webhook log was created
        $webhookLog = WebhookLog::find($webhookLogId);
        $this->assertNotNull($webhookLog);
        $this->assertEquals($order->id, $webhookLog->order_uuid);
        $this->assertEquals('tx_test_123', $webhookLog->txn_id);
        
        // Process the webhook job manually in test environment
        $job = new ProcessPaymentWebhookJob($webhookLogId);
        $job->handle();
        
        // Refresh webhook log after processing
        $webhookLog->refresh();
        $this->assertEquals('processed', $webhookLog->status);

        // Refresh models from database
        $order->refresh();
        $webhookLog->refresh();

        // Assert order status was updated
        $this->assertEquals('paid', $order->status);
        $this->assertEquals('tx_test_123', $order->external_txn_id);
        $this->assertEquals(100.00, $order->amount);

        // Assert webhook log status was updated
        $this->assertEquals('processed', $webhookLog->status);

        // Assert order event was created
        $orderEvent = OrderEvent::where('order_id', $order->id)->first();
        $this->assertNotNull($orderEvent);
        $this->assertEquals('pending', $orderEvent->from_status);
        $this->assertEquals('paid', $orderEvent->to_status);
        $this->assertEquals('webhook_payment', $orderEvent->reason);
        $this->assertArrayHasKey('webhook_log_id', $orderEvent->metadata);
        $this->assertEquals($webhookLogId, $orderEvent->metadata['webhook_log_id']);
    }

    /**
     * Test duplicate webhook with same txn_id is ignored
     */
    public function test_duplicate_webhook_with_same_txn_id_is_ignored(): void
    {
        // Create test user and order
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'amount' => 100.00,
            'status' => 'pending',
            'metadata' => ['product' => 'Test Product'],
        ]);

        $txnId = 'tx_duplicate_test';

        // First webhook request
        $webhookData1 = [
            'txn_id' => $txnId,
            'order_uuid' => $order->id,
            'status' => 'paid',
            'amount' => 100.00,
            'timestamp' => '2025-09-15T10:00:00Z',
            'metadata' => ['first' => true],
        ];

        $response1 = $this->postJson('/api/webhooks/payments', $webhookData1);
        $response1->assertStatus(202);
        $webhookLogId1 = $response1->json('webhook_log_id');

        // Process first webhook
        $job1 = new ProcessPaymentWebhookJob($webhookLogId1);
        $job1->handle();

        // Verify first webhook was processed
        $order->refresh();
        $this->assertEquals('paid', $order->status);
        $this->assertEquals($txnId, $order->external_txn_id);

        // Second webhook request with same txn_id
        $webhookData2 = [
            'txn_id' => $txnId, // Same transaction ID
            'order_uuid' => $order->id,
            'status' => 'paid',
            'amount' => 100.00,
            'timestamp' => '2025-09-15T10:05:00Z',
            'metadata' => ['second' => true],
        ];

        $response2 = $this->postJson('/api/webhooks/payments', $webhookData2);
        
        // CRITICAL: With our optimization, duplicate webhooks are now detected immediately via cache
        // and return 409 Conflict status instead of being processed
        $response2->assertStatus(409);
        $response2->assertJson([
            'message' => 'Duplicate webhook detected',
            'status' => 'ignored',
        ]);

        // Verify no webhook log was created for the duplicate
        $this->assertDatabaseMissing('webhook_logs', [
            'txn_id' => $txnId,
            'status' => 'pending',
        ]);

        // Verify order was not modified by second webhook
        $order->refresh();
        $this->assertEquals('paid', $order->status);
        $this->assertEquals($txnId, $order->external_txn_id);

        // Verify only one order event exists (from first webhook)
        $orderEvents = OrderEvent::where('order_id', $order->id)->get();
        $this->assertCount(1, $orderEvents);
        $this->assertEquals('pending', $orderEvents->first()->from_status);
        $this->assertEquals('paid', $orderEvents->first()->to_status);
    }

    /**
     * Test webhook for non-existent order is handled gracefully
     */
    public function test_webhook_for_non_existent_order_fails(): void
    {
        $nonExistentOrderUuid = '01994ddb-0000-0000-0000-000000000000';

        // Webhook request for non-existent order
        $webhookData = [
            'txn_id' => 'tx_out_of_order',
            'order_uuid' => $nonExistentOrderUuid,
            'status' => 'paid',
            'amount' => 100.00,
            'timestamp' => '2025-09-15T10:00:00Z',
            'metadata' => ['out_of_order' => true],
        ];

        // Send webhook request
        $response = $this->postJson('/api/webhooks/payments', $webhookData);

        // Assert webhook was accepted (we accept all webhooks)
        $response->assertStatus(202)
                ->assertJson([
                    'message' => 'Webhook received and queued for processing',
                ]);

        $webhookLogId = $response->json('webhook_log_id');

        // Assert webhook log was created
        $webhookLog = WebhookLog::find($webhookLogId);
        $this->assertNotNull($webhookLog);
        $this->assertEquals($nonExistentOrderUuid, $webhookLog->order_uuid);
        
        // In test environment with sync queue, the job processes immediately
        $this->assertEquals('failed', $webhookLog->status);

        // Refresh webhook log from database
        $webhookLog->refresh();

        // Assert webhook log was marked as failed
        $this->assertEquals('failed', $webhookLog->status);
        $this->assertEquals('Order not found', $webhookLog->last_error);

        // Assert no order was created
        $this->assertNull(Order::find($nonExistentOrderUuid));

        // Assert no order events were created
        $orderEvents = OrderEvent::where('order_id', $nonExistentOrderUuid)->get();
        $this->assertCount(0, $orderEvents);
    }

    /**
     * Test webhook validation rejects invalid data
     */
    public function test_webhook_validation_rejects_invalid_data(): void
    {
        // Test with invalid data
        $invalidData = [
            'txn_id' => '', // Empty txn_id
            'order_uuid' => 'invalid-uuid', // Invalid UUID
            'status' => 'invalid_status', // Invalid status
            'amount' => -10, // Negative amount
            'timestamp' => 'invalid-date', // Invalid date
            'metadata' => 'not-an-array', // Invalid metadata
        ];

        $response = $this->postJson('/api/webhooks/payments', $invalidData);

        // Assert validation failed
        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'txn_id',
                    'order_uuid',
                    'status',
                    'amount',
                    'timestamp',
                    'metadata',
                ]);

        // Assert no webhook log was created
        $this->assertEquals(0, WebhookLog::count());
    }

    /**
     * Test invalid status transition is ignored
     */
    public function test_invalid_status_transition_is_ignored(): void
    {
        // Create test user and order
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'amount' => 100.00,
            'status' => 'refunded', // Start with refunded status
            'metadata' => ['product' => 'Test Product'],
        ]);

        // Try to change from refunded to paid (invalid transition)
        $webhookData = [
            'txn_id' => 'tx_invalid_transition',
            'order_uuid' => $order->id,
            'status' => 'paid', // Invalid: can't go from refunded to paid
            'amount' => 100.00,
            'timestamp' => '2025-09-15T10:00:00Z',
            'metadata' => ['invalid_transition' => true],
        ];

        $response = $this->postJson('/api/webhooks/payments', $webhookData);
        $response->assertStatus(202);

        $webhookLogId = $response->json('webhook_log_id');

        // Process the job
        $job = new ProcessPaymentWebhookJob($webhookLogId);
        $job->handle();

        // Verify webhook was ignored due to invalid transition
        $webhookLog = WebhookLog::find($webhookLogId);
        $this->assertEquals('ignored', $webhookLog->status);
        $this->assertStringContainsString('Invalid status transition', $webhookLog->last_error);

        // Verify order status was not changed
        $order->refresh();
        $this->assertEquals('refunded', $order->status);
        $this->assertNull($order->external_txn_id);

        // Verify no order event was created
        $orderEvents = OrderEvent::where('order_id', $order->id)->get();
        $this->assertCount(0, $orderEvents);
    }

    /**
     * Test API endpoints return correct responses
     */
    public function test_api_endpoints_return_correct_responses(): void
    {
        // Test orders endpoint
        $response = $this->getJson('/api/orders');
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data',
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                        'from',
                        'to',
                        'has_more_pages'
                    ]
                ]);

        // Test orders endpoint with filters
        $response = $this->getJson('/api/orders?status=pending&per_page=10');
        $response->assertStatus(200);
    }
}
