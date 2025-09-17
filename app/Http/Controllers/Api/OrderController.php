<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderEventResource;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display the specified order.
     */
    public function show(string $uuid): JsonResponse|OrderResource
    {
        // Use select to only fetch needed columns for better performance
        $order = Order::select([
            'id', 'user_id', 'amount', 'status', 
            'external_txn_id', 'metadata', 'created_at', 'updated_at'
        ])->find($uuid);
        
        if (!$order) {
            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }

        return new OrderResource($order);
    }

    /**
     * Display the events for the specified order with pagination.
     */
    public function events(string $uuid, Request $request): JsonResponse
    {
        $order = Order::select(['id'])->find($uuid);
        
        if (!$order) {
            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }

        // Get pagination parameters
        $perPage = min($request->get('per_page', 50), 100); // Max 100 per page
        $page = $request->get('page', 1);

        // Use pagination for better performance with large datasets
        $events = $order->events()
            ->select([
                'id', 'order_id', 'from_status', 'to_status', 
                'reason', 'metadata', 'created_at', 'updated_at'
            ])
            ->orderBy('created_at', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'order_id' => $order->id,
            'events' => OrderEventResource::collection($events->items()),
            'pagination' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
                'from' => $events->firstItem(),
                'to' => $events->lastItem(),
                'has_more_pages' => $events->hasMorePages(),
            ],
        ]);
    }

    /**
     * List orders with filtering and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $startTime = microtime(true);
        
        // CRITICAL: Input validation and sanitization
        $perPage = min(max($request->get('per_page', 20), 1), 100); // Min 1, Max 100
        $page = max($request->get('page', 1), 1); // Min page 1
        
        // CRITICAL: Generate cache key for query results
        $cacheKey = 'orders:' . md5(serialize($request->all()));
        
        // CRITICAL: Check cache first for frequently accessed queries
        if ($request->get('cache', true) && \Cache::has($cacheKey)) {
            $cachedData = \Cache::get($cacheKey);
            $cachedData['cached'] = true;
            $cachedData['response_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);
            return response()->json($cachedData);
        }

        try {
            // CRITICAL: Use select to reduce memory usage
            $query = Order::select([
                'id', 'user_id', 'amount', 'status', 
                'external_txn_id', 'metadata', 'created_at', 'updated_at'
            ]);

            // CRITICAL: Apply filters with indexed columns only
            if ($request->has('status') && in_array($request->get('status'), ['pending', 'paid', 'refunded', 'failed'])) {
                $query->where('status', $request->get('status'));
            }

            if ($request->has('user_id') && is_numeric($request->get('user_id'))) {
                $query->where('user_id', $request->get('user_id'));
            }

            if ($request->has('has_external_txn_id')) {
                if ($request->get('has_external_txn_id')) {
                    $query->whereNotNull('external_txn_id');
                } else {
                    $query->whereNull('external_txn_id');
                }
            }

            // CRITICAL: Date range filtering with indexed columns
            if ($request->has('date_from')) {
                $query->where('created_at', '>=', $request->get('date_from'));
            }

            if ($request->has('date_to')) {
                $query->where('created_at', '<=', $request->get('date_to'));
            }

            // CRITICAL: Amount range filtering
            if ($request->has('amount_min') && is_numeric($request->get('amount_min'))) {
                $query->where('amount', '>=', $request->get('amount_min'));
            }
            
            if ($request->has('amount_max') && is_numeric($request->get('amount_max'))) {
                $query->where('amount', '<=', $request->get('amount_max'));
            }

            // CRITICAL: Apply sorting with indexed columns
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            if (in_array($sortBy, ['created_at', 'updated_at', 'amount', 'status'])) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc'); // Default to indexed column
            }

            // CRITICAL: Random ordering optimization
            if ($request->get('random')) {
                $query->inRandomOrder();
            }

            // CRITICAL: Use cursor pagination for large datasets
            if ($request->get('use_cursor', false)) {
                $orders = $query->cursorPaginate($perPage);
                $pagination = [
                    'per_page' => $perPage,
                    'has_more_pages' => $orders->hasMorePages(),
                    'next_cursor' => $orders->nextCursor()?->encode(),
                ];
            } else {
                // CRITICAL: Standard pagination with optimized queries
                $orders = $query->paginate($perPage, ['*'], 'page', $page);
                $pagination = [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'from' => $orders->firstItem(),
                    'to' => $orders->lastItem(),
                    'has_more_pages' => $orders->hasMorePages(),
                ];
            }

            $responseData = [
                'data' => OrderResource::collection($orders->items()),
                'pagination' => $pagination,
                'query_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'cached' => false,
            ];

            // CRITICAL: Cache frequently accessed queries
            if ($request->get('cache', true)) {
                \Cache::put($cacheKey, $responseData, 300); // 5 minutes cache
            }

            return response()->json($responseData);

        } catch (\Exception $e) {
            \Log::error("Order listing failed", [
                'error' => $e->getMessage(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to retrieve orders',
                'error' => 'Internal server error',
            ], 500);
        }
    }
}
