<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management + Webhooks Documentation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
    <style>
        .code-block {
            background: linear-gradient(135deg, #1e1e1e 0%, #2d2d2d 100%);
            color: #d4d4d4;
            border-radius: 12px;
            padding: 1.5rem;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', 'Consolas', monospace;
            font-size: 14px;
            line-height: 1.6;
            overflow-x: auto;
            border: 1px solid #3a3a3a;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
            position: relative;
            margin: 1rem 0;
        }
        
        .code-block::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6, #06b6d4);
            border-radius: 12px 12px 0 0;
        }
        
        .code-block pre {
            margin: 0;
            padding: 0;
            background: none;
            border: none;
            font-size: inherit;
            line-height: inherit;
        }
        
        .code-block code {
            background: none;
            padding: 0;
            border: none;
            font-size: inherit;
            color: inherit;
        }
        
        /* Syntax highlighting for PHP */
        .code-block .php-keyword { color: #c678dd; font-weight: bold; }
        .code-block .php-string { color: #98c379; }
        .code-block .php-comment { color: #5c6370; font-style: italic; }
        .code-block .php-variable { color: #e06c75; }
        .code-block .php-function { color: #61dafb; }
        .code-block .php-class { color: #e5c07b; }
        .code-block .php-number { color: #d19a66; }
        
        /* Syntax highlighting for JSON */
        .code-block .json-key { color: #e06c75; }
        .code-block .json-string { color: #98c379; }
        .code-block .json-number { color: #d19a66; }
        .code-block .json-boolean { color: #c678dd; }
        .code-block .json-null { color: #5c6370; }
        
        /* Copy button styling */
        .copy-btn {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #d4d4d4;
            padding: 0.5rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 12px;
            opacity: 0;
            transform: translateY(-5px);
        }
        
        .code-block:hover .copy-btn {
            opacity: 1;
            transform: translateY(0);
        }
        
        .copy-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        .copy-btn.copied {
            background: #10b981;
            border-color: #059669;
            color: white;
        }
        
        .section-anchor {
            scroll-margin-top: 100px;
        }
        
        .nav-link {
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            background-color: #f3f4f6;
            transform: translateX(4px);
        }
        
        /* Enhanced code explanation styling */
        .code-explanation {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-left: 4px solid #3b82f6;
            padding: 1rem;
            margin-top: 0.5rem;
            border-radius: 0 8px 8px 0;
            font-size: 14px;
            color: #475569;
        }
        
        .code-explanation strong {
            color: #1e293b;
            font-weight: 600;
        }
        
        /* Language indicator */
        .code-lang {
            position: absolute;
            top: 0.75rem;
            left: 0.75rem;
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Video styling */
        video {
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        video:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px -5px rgba(0, 0, 0, 0.15), 0 20px 20px -5px rgba(0, 0, 0, 0.08);
        }
        
        /* Video container styling */
        .video-container {
            position: relative;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 16px;
            padding: 1.5rem;
            margin: 1rem 0;
            border: 1px solid #e2e8f0;
        }
        
        .video-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #ef4444, #3b82f6);
            border-radius: 16px 16px 0 0;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation Sidebar -->
    <nav class="fixed left-0 top-0 h-full w-64 bg-white shadow-lg overflow-y-auto z-10">
        <div class="p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-6">📚 Documentation</h2>
            <ul class="space-y-2">
                <li><a href="#greeting" class="nav-link block px-3 py-2 text-gray-700 rounded">👋 Greeting</a></li>
                <li><a href="#demo" class="nav-link block px-3 py-2 text-gray-700 rounded">📹 Demo & Resources</a></li>
                <li><a href="#overview" class="nav-link block px-3 py-2 text-gray-700 rounded">📖 Project Overview</a></li>
                <li><a href="#challenges" class="nav-link block px-3 py-2 text-gray-700 rounded">⚡ Challenges</a></li>
                <li><a href="#solutions" class="nav-link block px-3 py-2 text-gray-700 rounded">🛠️ Solutions & Code</a></li>
                <li><a href="#architecture" class="nav-link block px-3 py-2 text-gray-700 rounded">🔄 Architecture</a></li>
                <li><a href="#advantages" class="nav-link block px-3 py-2 text-gray-700 rounded">🌟 Advantages</a></li>
                <li><a href="#examples" class="nav-link block px-3 py-2 text-gray-700 rounded">📝 Examples</a></li>
                <li><a href="#quickstart" class="nav-link block px-3 py-2 text-gray-700 rounded">🚀 Quick Start</a></li>
            </ul>
                </div>
    </nav>

    <!-- Main Content -->
    <main class="ml-64 p-8">
        <div class="max-w-5xl mx-auto">
            
            <!-- 1. Greeting / Introduction -->
            <section id="greeting" class="section-anchor mb-16">
                <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white p-8 rounded-lg shadow-lg">
                    <h1 class="text-4xl font-bold mb-4">👋 Welcome to Order Management + Webhooks</h1>
                    <p class="text-xl leading-relaxed">
                        A robust, scalable PHP application designed to handle high-volume order processing with webhook integration. 
                        This system demonstrates enterprise-level patterns for handling webhooks, ensuring idempotency, managing concurrency, 
                        and maintaining comprehensive audit trails.
                    </p>
                </div>
            </section>

            <!-- 2. Demo & Resources -->
            <section id="demo" class="section-anchor mb-16">
                <h2 class="text-3xl font-bold text-gray-800 mb-6">📹 Demo & Resources</h2>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="text-center">
                            <div class="video-container">
                                <h3 class="text-xl font-semibold text-red-800 mb-2">🎥 Discussion Video</h3>
                                <p class="text-red-700 mb-4">System Overview & Discussion</p>
                                <video controls class="w-full max-w-md mx-auto mb-4">
                                    <source src="/falsafa-raqamia-v1.webm" type="video/webm">
                                    Your browser does not support the video tag.
                                </video>
                                <div class="space-y-2">
                                    <a href="/falsafa-raqamia-v1.webm" download="falsafa-raqamia-discussion.webm" class="inline-block bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700 transition">
                                        📥 Download Discussion Video
                                    </a>
                                    <p class="text-sm text-gray-600">Duration: ~15 minutes</p>
                                </div>
                            </div>
                    </div>
                    
                    <div class="text-center">
                            <div class="video-container">
                                <h3 class="text-xl font-semibold text-blue-800 mb-2">🎥 Code Review Video</h3>
                                <p class="text-blue-700 mb-4">Code Review & Technical Implementation</p>
                                <video controls class="w-full max-w-md mx-auto mb-4">
                                    <source src="/falsafa-raqamia-v2.webm" type="video/webm">
                                    Your browser does not support the video tag.
                                </video>
                                <div class="space-y-2">
                                    <a href="/falsafa-raqamia-v2.webm" download="falsafa-raqamia-code-review.webm" class="inline-block bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
                                        📥 Download Code Review Video
                                    </a>
                                    <p class="text-sm text-gray-600">Duration: ~13 minutes</p>
                                </div>
                            </div>
                    </div>
                    
                        <div class="text-center">
                            <div class="bg-blue-100 p-6 rounded-lg mb-4">
                                <h3 class="text-xl font-semibold text-blue-800 mb-2">📋 Postman Collection</h3>
                                <p class="text-blue-700 mb-4">Test all endpoints</p>
                                <div class="space-y-2">
                                    <a href="/postman-collection.json" download="falsafa-raqamia-postman-collection.json" class="inline-block bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
                                        📥 Download Collection
                                    </a>
                        </div>
                    </div>
                </div>
                        
                        <div class="text-center">
                            <div class="bg-green-100 p-6 rounded-lg mb-4">
                                <h3 class="text-xl font-semibold text-green-800 mb-2">🐙 GitHub Repository</h3>
                                <p class="text-green-700 mb-4">View source code</p>
                                <a href="https://github.com/ahmedayman220/falsafa-raqamia.maksoudaa.com" target="_blank" rel="noopener noreferrer" class="inline-block bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition">
                                    View on GitHub
                                </a>
                        </div>
                    </div>
                    
                        <div class="text-center">
                            <div class="bg-purple-100 p-6 rounded-lg mb-4">
                                <h3 class="text-xl font-semibold text-purple-800 mb-2">📚 API Documentation</h3>
                                <p class="text-purple-700 mb-4">Detailed API reference</p>
                                <a href="https://www.postman.com/nasa33/workspace/falsafa-rqamia/collection/33530840-5349c8dd-908a-47be-a96e-0697f8000f38?action=share&source=copy-link&creator=33530840" target="_blank" rel="noopener noreferrer" class="inline-block bg-purple-600 text-white px-6 py-2 rounded hover:bg-purple-700 transition">
                                    View Documentation
                                </a>
                            </div>
                    </div>
                </div>
            </div>
        </section>

            <!-- 3. Project Overview -->
            <section id="overview" class="section-anchor mb-16">
                <h2 class="text-3xl font-bold text-gray-800 mb-6">📖 Project Overview</h2>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <p class="text-lg text-gray-700 mb-4">
                        This PHP application provides a comprehensive order management system with webhook processing capabilities. 
                        It's designed to handle high-pressure scenarios with 20M+ records while maintaining data integrity and performance.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-blue-800 mb-2">Core Features</h3>
                            <ul class="text-blue-700 space-y-1">
                                <li>• Order Management with Status Tracking</li>
                                <li>• Webhook Processing with Retry Logic</li>
                                <li>• Comprehensive Audit Trail</li>
                                <li>• Queue Processing & Background Jobs</li>
                                <li>• Idempotency Protection</li>
                                <li>• Concurrency Control</li>
                            </ul>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-green-800 mb-2">Technical Highlights</h3>
                            <ul class="text-green-700 space-y-1">
                                <li>• Database Transactions with Optimistic Locking</li>
                                <li>• Advanced Rate Limiting & Performance Optimization</li>
                                <li>• Database Indexing for Scale</li>
                                <li>• Comprehensive Logging & Monitoring</li>
                                <li>• Error Handling & Recovery</li>
                                <li>• Health Check Endpoints</li>
                            </ul>
                        </div>
                    </div>
                </div>
        </section>

            <!-- 4. Challenges in the Task -->
            <section id="challenges" class="section-anchor mb-16">
                <h2 class="text-3xl font-bold text-gray-800 mb-6">⚡ Challenges in the Task</h2>
                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-red-500">
                        <h3 class="text-xl font-semibold text-red-800 mb-3">🔄 Handling Webhooks</h3>
                        <p class="text-gray-700">
                            Webhooks can arrive out of order, be duplicated, or fail during processing. The system must handle these scenarios gracefully 
                            while maintaining data consistency and providing reliable processing.
                        </p>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-orange-500">
                        <h3 class="text-xl font-semibold text-orange-800 mb-3">🔒 Idempotency</h3>
                        <p class="text-gray-700">
                            Ensuring that processing the same webhook multiple times doesn't result in duplicate data or incorrect state changes. 
                            Critical for maintaining data integrity in distributed systems.
                        </p>
                    </div>
                
                    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-500">
                        <h3 class="text-xl font-semibold text-yellow-800 mb-3">⚡ Concurrency / Race Conditions</h3>
                        <p class="text-gray-700">
                            Multiple webhooks processing simultaneously can lead to race conditions, especially when updating order statuses. 
                            The system must handle concurrent access safely without data corruption.
                        </p>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500">
                        <h3 class="text-xl font-semibold text-blue-800 mb-3">📋 Queue Processing & Retries</h3>
                        <p class="text-gray-700">
                            Background job processing with intelligent retry mechanisms, exponential backoff, and failure handling. 
                            Essential for reliable webhook processing at scale.
                        </p>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-purple-500">
                        <h3 class="text-xl font-semibold text-purple-800 mb-3">📊 Audit Trail</h3>
                        <p class="text-gray-700">
                            Complete tracking of all order status changes, webhook processing events, and system activities. 
                            Provides transparency and debugging capabilities for production systems.
                        </p>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500">
                        <h3 class="text-xl font-semibold text-green-800 mb-3">📦 Out-of-Order Webhook Delivery</h3>
                        <p class="text-gray-700">
                            Webhooks may arrive in different order than they were sent, requiring intelligent handling to maintain 
                            correct order state transitions and business logic.
                        </p>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-red-500">
                        <h3 class="text-xl font-semibold text-red-800 mb-3">🚦 Rate Limitation & System Overload</h3>
                        <p class="text-gray-700">
                            High-volume webhook scenarios (20M+ records) can overwhelm the system, causing performance degradation, 
                            timeouts, and potential service unavailability. Without proper rate limiting, the system becomes vulnerable 
                            to DDoS attacks and resource exhaustion.
                        </p>
                        <div class="mt-4 p-4 bg-red-50 rounded-lg">
                            <h4 class="font-semibold text-red-800 mb-2">Specific Problems:</h4>
                            <ul class="text-red-700 space-y-1 text-sm">
                                <li>• <strong>Resource Exhaustion:</strong> Unlimited requests can consume all server resources</li>
                                <li>• <strong>Database Overload:</strong> Too many concurrent database operations</li>
                                <li>• <strong>Memory Issues:</strong> Excessive memory usage from concurrent processing</li>
                                <li>• <strong>Service Degradation:</strong> Slow response times affecting legitimate users</li>
                                <li>• <strong>Security Vulnerabilities:</strong> Potential for abuse and DDoS attacks</li>
                            </ul>
                        </div>
                </div>
            </div>
        </section>

            <!-- 5. Solutions & Code -->
            <section id="solutions" class="section-anchor mb-16">
                <h2 class="text-3xl font-bold text-gray-800 mb-6">🛠️ Solutions & Code</h2>
                
                <!-- Webhook Handling Solution -->
                <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">🔄 Webhook Handling Solution</h3>
                    <p class="text-gray-700 mb-4">
                        The webhook controller implements rate limiting, duplicate detection, and immediate queuing for database transaction-based processing.
                    </p>
                    <div class="code-block">
                        <div class="code-lang">PHP</div>
                        <button class="copy-btn" onclick="copyCode(this)">📋 Copy</button>
                        <pre><code><span class="php-comment">// routes/api.php - Rate limiting via middleware</span>
<span class="php-class">Route</span>::<span class="php-function">post</span>(<span class="php-string">'/webhooks/payments'</span>, [<span class="php-class">WebhookController</span>::<span class="php-keyword">class</span>, <span class="php-string">'store'</span>])
    -><span class="php-function">middleware</span>(<span class="php-string">'webhook.rate.limit:1000,1'</span>);

<span class="php-comment">// WebhookController.php - Clean separation of concerns</span>
<span class="php-keyword">public function</span> <span class="php-function">store</span>(<span class="php-class">WebhookPaymentRequest</span> <span class="php-variable">$request</span>): <span class="php-class">JsonResponse</span>
{
    <span class="php-variable">$startTime</span> = <span class="php-function">microtime</span>(<span class="php-keyword">true</span>);
    
    <span class="php-comment">// CRITICAL: Duplicate detection using cache for high performance</span>
    <span class="php-variable">$validatedData</span> = <span class="php-variable">$request</span>-><span class="php-function">validated</span>();
    <span class="php-variable">$txnId</span> = <span class="php-variable">$validatedData</span>[<span class="php-string">'txn_id'</span>];
    <span class="php-variable">$cacheKey</span> = <span class="php-string">"webhook_txn:{<span class="php-variable">$txnId</span>}"</span>;
    
    <span class="php-keyword">if</span> (<span class="php-class">Cache</span>::<span class="php-function">has</span>(<span class="php-variable">$cacheKey</span>)) {
        <span class="php-keyword">return</span> <span class="php-function">response</span>()-><span class="php-function">json</span>([<span class="php-string">'message'</span> => <span class="php-string">'Duplicate webhook detected'</span>], <span class="php-number">409</span>);
    }
    
    <span class="php-comment">// CRITICAL: Create webhook log and dispatch job with high priority</span>
    <span class="php-variable">$webhookLog</span> = <span class="php-class">WebhookLog</span>::<span class="php-function">create</span>([...]);
    <span class="php-class">ProcessPaymentWebhookJob</span>::<span class="php-function">dispatch</span>(<span class="php-variable">$webhookLog</span>-><span class="php-function">id</span>)
        -><span class="php-function">onQueue</span>(<span class="php-string">'webhooks-high-priority'</span>);
    
    <span class="php-variable">$processingTime</span> = <span class="php-function">round</span>((<span class="php-function">microtime</span>(<span class="php-keyword">true</span>) - <span class="php-variable">$startTime</span>) * <span class="php-number">1000</span>, <span class="php-number">2</span>);
    
    <span class="php-keyword">return</span> <span class="php-function">response</span>()-><span class="php-function">json</span>([
        <span class="php-string">'message'</span> => <span class="php-string">'Webhook received and queued for processing'</span>,
        <span class="php-string">'processing_time_ms'</span> => <span class="php-variable">$processingTime</span>,
    ], <span class="php-number">202</span>);
}</code></pre>
                        </div>
                    <div class="code-explanation">
                        <strong>Explanation:</strong> Rate limiting middleware provides protection, cache-based duplicate detection ensures idempotency, 
                        and immediate job dispatch enables asynchronous processing with database transaction-based concurrency control.
                        </div>
                        </div>

                <!-- Idempotency Solution -->
                <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">🔒 Idempotency Solution</h3>
                    <p class="text-gray-700 mb-4">
                        Multiple layers of idempotency protection using transaction IDs and database constraints.
                    </p>
                    <div class="code-block">
                        <div class="code-lang">PHP</div>
                        <button class="copy-btn" onclick="copyCode(this)">📋 Copy</button>
                        <pre><code><span class="php-comment">// Check if external_txn_id already exists</span>
<span class="php-keyword">if</span> (<span class="php-variable">$payload</span>[<span class="php-string">'txn_id'</span>] && <span class="php-class">Order</span>::<span class="php-function">withExternalTxnId</span>(<span class="php-variable">$payload</span>[<span class="php-string">'txn_id'</span>])-><span class="php-function">exists</span>()) {
    <span class="php-class">Log</span>::<span class="php-function">info</span>(<span class="php-string">"Transaction ID {<span class="php-variable">$payload</span>[<span class="php-string">'txn_id'</span>]} already processed, ignoring webhook"</span>);
    <span class="php-variable">$webhookLog</span>-><span class="php-function">markAsIgnored</span>(<span class="php-string">'Transaction already processed'</span>);
    <span class="php-keyword">return</span>;
}

<span class="php-comment">// Cache-based duplicate prevention</span>
<span class="php-class">Cache</span>::<span class="php-function">put</span>(<span class="php-variable">$cacheKey</span>, <span class="php-string">true</span>, <span class="php-number">300</span>); <span class="php-comment">// 5 minutes cache</span>

<span class="php-comment">// Database unique constraint on external_txn_id</span>
<span class="php-class">Schema</span>::<span class="php-function">table</span>(<span class="php-string">'orders'</span>, <span class="php-keyword">function</span> (<span class="php-class">Blueprint</span> <span class="php-variable">$table</span>) {
    <span class="php-variable">$table</span>-><span class="php-function">unique</span>(<span class="php-string">'external_txn_id'</span>);
});</code></pre>
                    </div>
                    <div class="code-explanation">
                        <strong>Explanation:</strong> Three-layer protection: cache check, database query, and unique constraint 
                        ensure no duplicate transactions are processed.
                </div>
            </div>

                <!-- Concurrency Solution -->
                <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">⚡ Concurrency Solution</h3>
                    <p class="text-gray-700 mb-4">
                        Database transaction with optimistic locking strategy prevents race conditions while maintaining ACID compliance.
                    </p>
                    <div class="code-block">
                        <div class="code-lang">PHP</div>
                        <button class="copy-btn" onclick="copyCode(this)">📋 Copy</button>
                        <pre><code><span class="php-comment">// CRITICAL: Use database transaction with optimistic locking</span>
<span class="php-class">DB</span>::<span class="php-function">transaction</span>(<span class="php-keyword">function</span> () <span class="php-keyword">use</span> (<span class="php-variable">$webhookLog</span>, <span class="php-variable">$payload</span>, <span class="php-variable">$startTime</span>) {
    <span class="php-comment">// CRITICAL: Get order without locking (optimistic approach)</span>
    <span class="php-variable">$order</span> = <span class="php-class">Order</span>::<span class="php-function">where</span>(<span class="php-string">'id'</span>, <span class="php-variable">$payload</span>[<span class="php-string">'order_uuid'</span>])-><span class="php-function">first</span>();
    
    <span class="php-comment">// CRITICAL: Optimistic locking - update with version check</span>
    <span class="php-variable">$updatedRows</span> = <span class="php-class">Order</span>::<span class="php-function">where</span>(<span class="php-string">'id'</span>, <span class="php-variable">$order</span>-><span class="php-function">id</span>)
        -><span class="php-function">where</span>(<span class="php-string">'updated_at'</span>, <span class="php-variable">$order</span>-><span class="php-function">updated_at</span>) <span class="php-comment">// Optimistic lock</span>
        -><span class="php-function">update</span>([
            <span class="php-string">'status'</span> => <span class="php-variable">$newStatus</span>,
            <span class="php-string">'external_txn_id'</span> => <span class="php-variable">$payload</span>[<span class="php-string">'txn_id'</span>],
            <span class="php-string">'amount'</span> => <span class="php-variable">$payload</span>[<span class="php-string">'amount'</span>],
            <span class="php-string">'updated_at'</span> => <span class="php-function">now</span>(),
        ]);

    <span class="php-comment">// CRITICAL: Check if update succeeded (optimistic locking)</span>
    <span class="php-keyword">if</span> (<span class="php-variable">$updatedRows</span> === <span class="php-number">0</span>) {
        <span class="php-class">Log</span>::<span class="php-function">warning</span>(<span class="php-string">"Optimistic lock failed for order {<span class="php-variable">$order</span>-><span class="php-function">id</span>} - another process modified it"</span>);
        <span class="php-keyword">throw new</span> <span class="php-class">\Exception</span>(<span class="php-string">'Optimistic lock failed - order was modified by another process'</span>);
    }
    
    <span class="php-comment">// Create audit trail and update webhook log within same transaction</span>
    <span class="php-class">OrderEvent</span>::<span class="php-function">create</span>([...]);
    <span class="php-variable">$webhookLog</span>-><span class="php-function">markAsProcessed</span>(<span class="php-variable">$processingTime</span>);
});</code></pre>
                        </div>
                    <div class="code-explanation">
                        <strong>Explanation:</strong> Uses <code>DB::transaction()</code> for ACID compliance with optimistic locking. 
                        The updated_at timestamp serves as a version field. If another process modified the record, 
                        the update returns 0 rows and triggers a retry via Laravel's job retry mechanism.
                        </div>
                    </div>
                    
                <!-- Queue Processing Solution -->
                <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">📋 Queue Processing Solution</h3>
                    <p class="text-gray-700 mb-4">
                        Database transaction-based processing with intelligent retry mechanism and comprehensive error handling.
                    </p>
                    <div class="code-block">
                        <div class="code-lang">PHP</div>
                        <button class="copy-btn" onclick="copyCode(this)">📋 Copy</button>
                        <pre><code><span class="php-keyword">class</span> <span class="php-class">ProcessPaymentWebhookJob</span> <span class="php-keyword">implements</span> <span class="php-class">ShouldQueue</span>
{
    <span class="php-keyword">public</span> <span class="php-keyword">int</span> <span class="php-variable">$tries</span> = <span class="php-number">5</span>;
    <span class="php-keyword">public</span> <span class="php-keyword">int</span> <span class="php-variable">$timeout</span> = <span class="php-number">60</span>;
    
    <span class="php-comment">/**
     * Calculate the number of seconds to wait before retrying the job.
     * Optimized for database transaction conflicts and deadlocks.
     */</span>
    <span class="php-keyword">public function</span> <span class="php-function">backoff</span>(): <span class="php-keyword">array</span>
    {
        <span class="php-keyword">return</span> [<span class="php-number">5</span>, <span class="php-number">10</span>, <span class="php-number">30</span>, <span class="php-number">60</span>, <span class="php-number">120</span>]; <span class="php-comment">// 5s, 10s, 30s, 1m, 2m</span>
    }
    
    <span class="php-keyword">public function</span> <span class="php-function">handle</span>(): <span class="php-keyword">void</span>
    {
        <span class="php-keyword">try</span> {
            <span class="php-comment">// CRITICAL: Use database transaction with optimistic locking</span>
            <span class="php-class">DB</span>::<span class="php-function">transaction</span>(<span class="php-keyword">function</span> () <span class="php-keyword">use</span> (<span class="php-variable">$webhookLog</span>, <span class="php-variable">$payload</span>, <span class="php-variable">$startTime</span>) {
                <span class="php-comment">// Process webhook with optimistic locking</span>
                <span class="php-variable">$order</span> = <span class="php-class">Order</span>::<span class="php-function">where</span>(<span class="php-string">'id'</span>, <span class="php-variable">$payload</span>[<span class="php-string">'order_uuid'</span>])-><span class="php-function">first</span>();
                
                <span class="php-variable">$updatedRows</span> = <span class="php-class">Order</span>::<span class="php-function">where</span>(<span class="php-string">'id'</span>, <span class="php-variable">$order</span>-><span class="php-function">id</span>)
                    -><span class="php-function">where</span>(<span class="php-string">'updated_at'</span>, <span class="php-variable">$order</span>-><span class="php-function">updated_at</span>)
                    -><span class="php-function">update</span>([...]);
                
                <span class="php-keyword">if</span> (<span class="php-variable">$updatedRows</span> === <span class="php-number">0</span>) {
                    <span class="php-keyword">throw new</span> <span class="php-class">\Exception</span>(<span class="php-string">'Optimistic lock failed'</span>);
                }
            });
        } <span class="php-keyword">catch</span> (<span class="php-class">\Exception</span> <span class="php-variable">$e</span>) {
            <span class="php-comment">// Handle optimistic lock failures - these should be retried</span>
            <span class="php-keyword">if</span> (<span class="php-function">str_contains</span>(<span class="php-variable">$e</span>-><span class="php-function">getMessage</span>(), <span class="php-string">'Optimistic lock failed'</span>)) {
                <span class="php-class">Log</span>::<span class="php-function">warning</span>(<span class="php-string">"Optimistic lock failed for webhook {<span class="php-variable">$this</span>-><span class="php-function">webhookLogId</span>}, will retry"</span>);
                <span class="php-keyword">throw</span> <span class="php-variable">$e</span>; <span class="php-comment">// Re-throw to trigger retry mechanism</span>
            }
            <span class="php-keyword">throw</span> <span class="php-variable">$e</span>;
        }
    }
}</code></pre>
                </div>
                    <div class="code-explanation">
                        <strong>Explanation:</strong> Uses <code>DB::transaction()</code> for ACID compliance with optimistic locking. 
                        Shorter backoff delays (5s-2m) are optimized for transaction conflicts. Laravel's job retry mechanism 
                        automatically handles optimistic lock failures.
                        </div>
                    </div>
                    
                <!-- Audit Trail Solution -->
                <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">📊 Audit Trail Solution</h3>
                    <p class="text-gray-700 mb-4">
                        Comprehensive event logging with detailed metadata for complete traceability.
                    </p>
                    <div class="code-block">
                        <div class="code-lang">PHP</div>
                        <button class="copy-btn" onclick="copyCode(this)">📋 Copy</button>
                        <pre><code><span class="php-comment">// Insert new row in order_events (audit trail)</span>
<span class="php-class">OrderEvent</span>::<span class="php-function">create</span>([
    <span class="php-string">'order_id'</span> => <span class="php-variable">$order</span>-><span class="php-function">id</span>,
    <span class="php-string">'from_status'</span> => <span class="php-variable">$oldStatus</span>,
    <span class="php-string">'to_status'</span> => <span class="php-variable">$newStatus</span>,
    <span class="php-string">'reason'</span> => <span class="php-class">OrderEvent</span>::<span class="php-function">REASON_WEBHOOK_PAYMENT</span>,
    <span class="php-string">'metadata'</span> => [
        <span class="php-string">'webhook_log_id'</span> => <span class="php-variable">$webhookLog</span>-><span class="php-function">id</span>,
        <span class="php-string">'txn_id'</span> => <span class="php-variable">$payload</span>[<span class="php-string">'txn_id'</span>],
        <span class="php-string">'timestamp'</span> => <span class="php-variable">$payload</span>[<span class="php-string">'timestamp'</span>],
        <span class="php-string">'amount'</span> => <span class="php-variable">$payload</span>[<span class="php-string">'amount'</span>],
        <span class="php-string">'webhook_source'</span> => <span class="php-variable">$webhookLog</span>-><span class="php-function">webhook_source</span>,
        <span class="php-string">'correlation_id'</span> => <span class="php-variable">$webhookLog</span>-><span class="php-function">correlation_id</span>,
    ],
]);

<span class="php-comment">// Webhook log tracking</span>
<span class="php-variable">$webhookLog</span> = <span class="php-class">WebhookLog</span>::<span class="php-function">create</span>([
    <span class="php-string">'order_uuid'</span> => <span class="php-variable">$validatedData</span>[<span class="php-string">'order_uuid'</span>],
    <span class="php-string">'txn_id'</span> => <span class="php-variable">$txnId</span>,
    <span class="php-string">'raw_payload'</span> => <span class="php-variable">$validatedData</span>,
    <span class="php-string">'status'</span> => <span class="php-class">WebhookLog</span>::<span class="php-function">STATUS_PENDING</span>,
    <span class="php-string">'attempts'</span> => <span class="php-number">1</span>,
    <span class="php-string">'webhook_source'</span> => <span class="php-variable">$validatedData</span>[<span class="php-string">'webhook_source'</span>] ?? <span class="php-class">WebhookLog</span>::<span class="php-function">SOURCE_UNKNOWN</span>,
    <span class="php-string">'correlation_id'</span> => <span class="php-variable">$validatedData</span>[<span class="php-string">'correlation_id'</span>] ?? <span class="php-keyword">null</span>,
]);</code></pre>
                        </div>
                    <div class="code-explanation">
                        <strong>Explanation:</strong> Every status change is recorded in order_events with full context, 
                        while webhook_logs track all incoming webhooks and their processing status.
                </div>
            </div>

                <!-- Rate Limiting Solution -->
                <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">🚦 Rate Limiting Solution</h3>
                    <p class="text-gray-700 mb-4">
                        Multi-layered rate limiting strategy using Laravel's built-in rate limiter with IP-based throttling 
                        and configurable limits optimized for high-volume webhook scenarios.
                    </p>
                    
                    <!-- Middleware Implementation -->
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-gray-700 mb-3">1. Custom Rate Limiting Middleware</h4>
                        <div class="code-block">
                            <div class="code-lang">PHP</div>
                            <button class="copy-btn" onclick="copyCode(this)">📋 Copy</button>
                            <pre><code><span class="php-keyword">class</span> <span class="php-class">WebhookRateLimit</span>
{
    <span class="php-comment">/**
     * Handle an incoming request.
     * 
     * Rate limiting middleware specifically designed for webhook endpoints
     * to handle high-pressure scenarios with 20M+ records.
     */</span>
    <span class="php-keyword">public function</span> <span class="php-function">handle</span>(<span class="php-class">Request</span> <span class="php-variable">$request</span>, <span class="php-class">Closure</span> <span class="php-variable">$next</span>, <span class="php-keyword">int</span> <span class="php-variable">$maxAttempts</span> = <span class="php-number">1000</span>, <span class="php-keyword">int</span> <span class="php-variable">$decayMinutes</span> = <span class="php-number">1</span>): <span class="php-class">Response</span>
    {
        <span class="php-comment">// CRITICAL: Rate limiting for high-pressure webhook scenarios</span>
        <span class="php-variable">$key</span> = <span class="php-string">'webhook:'</span> . <span class="php-variable">$request</span>-><span class="php-function">ip</span>();
        
        <span class="php-keyword">if</span> (<span class="php-class">RateLimiter</span>::<span class="php-function">tooManyAttempts</span>(<span class="php-variable">$key</span>, <span class="php-variable">$maxAttempts</span>)) {
            <span class="php-keyword">return</span> <span class="php-function">response</span>()-><span class="php-function">json</span>([
                <span class="php-string">'message'</span> => <span class="php-string">'Too many requests'</span>,
                <span class="php-string">'retry_after'</span> => <span class="php-class">RateLimiter</span>::<span class="php-function">availableIn</span>(<span class="php-variable">$key</span>),
                <span class="php-string">'limit'</span> => <span class="php-variable">$maxAttempts</span>,
                <span class="php-string">'window'</span> => <span class="php-variable">$decayMinutes</span> . <span class="php-string">' minute(s)'</span>,
            ], <span class="php-number">429</span>);
        }
        
        <span class="php-comment">// Increment the rate limit counter</span>
        <span class="php-class">RateLimiter</span>::<span class="php-function">hit</span>(<span class="php-variable">$key</span>, <span class="php-variable">$decayMinutes</span> * <span class="php-number">60</span>);
        
        <span class="php-keyword">return</span> <span class="php-variable">$next</span>(<span class="php-variable">$request</span>);
    }
}</code></pre>
                        </div>
                        <div class="code-explanation">
                            <strong>Explanation:</strong> Custom middleware provides IP-based rate limiting with configurable limits. 
                            Default: 1000 requests per minute per IP, with clear error responses including retry timing.
                        </div>
                    </div>

                    <!-- Route Configuration -->
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-gray-700 mb-3">2. Route Configuration</h4>
                        <div class="code-block">
                            <div class="code-lang">PHP</div>
                            <button class="copy-btn" onclick="copyCode(this)">📋 Copy</button>
                            <pre><code><span class="php-comment">// routes/api.php - Rate limiting via middleware</span>
<span class="php-class">Route</span>::<span class="php-function">post</span>(<span class="php-string">'/webhooks/payments'</span>, [<span class="php-class">WebhookController</span>::<span class="php-keyword">class</span>, <span class="php-string">'store'</span>])
    -><span class="php-function">middleware</span>(<span class="php-string">'webhook.rate.limit:1000,1'</span>);

<span class="php-comment">// Alternative: More restrictive for sensitive endpoints</span>
<span class="php-class">Route</span>::<span class="php-function">post</span>(<span class="php-string">'/webhooks/admin'</span>, [<span class="php-class">AdminWebhookController</span>::<span class="php-keyword">class</span>, <span class="php-string">'store'</span>])
    -><span class="php-function">middleware</span>(<span class="php-string">'webhook.rate.limit:100,1'</span>); <span class="php-comment">// 100 requests per minute</span></code></pre>
                        </div>
                        <div class="code-explanation">
                            <strong>Explanation:</strong> Middleware parameters: first number = max attempts, second number = decay minutes. 
                            Different endpoints can have different rate limits based on their sensitivity.
                        </div>
                    </div>

                    <!-- Controller Integration -->
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-gray-700 mb-3">3. Controller Integration</h4>
                        <div class="code-block">
                            <div class="code-lang">PHP</div>
                            <button class="copy-btn" onclick="copyCode(this)">📋 Copy</button>
                            <pre><code><span class="php-keyword">public function</span> <span class="php-function">store</span>(<span class="php-class">WebhookPaymentRequest</span> <span class="php-variable">$request</span>): <span class="php-class">JsonResponse</span>
{
    <span class="php-variable">$startTime</span> = <span class="php-function">microtime</span>(<span class="php-keyword">true</span>);
    
    <span class="php-comment">// CRITICAL: Duplicate detection using cache for high performance</span>
    <span class="php-variable">$validatedData</span> = <span class="php-variable">$request</span>-><span class="php-function">validated</span>();
    <span class="php-variable">$txnId</span> = <span class="php-variable">$validatedData</span>[<span class="php-string">'txn_id'</span>];
    <span class="php-variable">$cacheKey</span> = <span class="php-string">"webhook_txn:{<span class="php-variable">$txnId</span>}"</span>;
    
    <span class="php-keyword">if</span> (<span class="php-class">Cache</span>::<span class="php-function">has</span>(<span class="php-variable">$cacheKey</span>)) {
        <span class="php-keyword">return</span> <span class="php-function">response</span>()-><span class="php-function">json</span>([
            <span class="php-string">'message'</span> => <span class="php-string">'Duplicate webhook detected'</span>,
            <span class="php-string">'status'</span> => <span class="php-string">'ignored'</span>,
            <span class="php-string">'txn_id'</span> => <span class="php-variable">$txnId</span>,
        ], <span class="php-number">409</span>);
    }

    <span class="php-comment">// Process webhook...</span>
    <span class="php-variable">$processingTime</span> = <span class="php-function">round</span>((<span class="php-function">microtime</span>(<span class="php-keyword">true</span>) - <span class="php-variable">$startTime</span>) * <span class="php-number">1000</span>, <span class="php-number">2</span>);
    
    <span class="php-keyword">return</span> <span class="php-function">response</span>()-><span class="php-function">json</span>([
        <span class="php-string">'message'</span> => <span class="php-string">'Webhook received and queued for processing'</span>,
        <span class="php-string">'processing_time_ms'</span> => <span class="php-variable">$processingTime</span>,
    ], <span class="php-number">202</span>);
}</code></pre>
                        </div>
                        <div class="code-explanation">
                            <strong>Explanation:</strong> Controller includes performance monitoring and fast duplicate detection 
                            to minimize processing time and prevent unnecessary work.
                        </div>
                    </div>

                    <!-- Rate Limiting Benefits -->
                    <div class="mt-6 p-4 bg-green-50 rounded-lg">
                        <h4 class="font-semibold text-green-800 mb-2">Rate Limiting Benefits:</h4>
                        <ul class="text-green-700 space-y-1 text-sm">
                            <li>• <strong>System Protection:</strong> Prevents resource exhaustion and service degradation</li>
                            <li>• <strong>Fair Usage:</strong> Ensures all clients get fair access to the API</li>
                            <li>• <strong>DDoS Mitigation:</strong> Protects against malicious traffic spikes</li>
                            <li>• <strong>Performance Optimization:</strong> Maintains consistent response times</li>
                            <li>• <strong>Cost Control:</strong> Prevents unexpected infrastructure costs</li>
                            <li>• <strong>Monitoring:</strong> Provides clear metrics on usage patterns</li>
                        </ul>
                    </div>
                </div>
        </section>

            <!-- 6. System Architecture & Flowcharts -->
            <section id="architecture" class="section-anchor mb-16">
                <h2 class="text-3xl font-bold text-gray-800 mb-6">🔄 System Architecture & Flowcharts</h2>
                
                <!-- System Architecture -->
                <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">System Architecture</h3>
                <div class="mermaid">
                    graph TB
    subgraph "External Systems"
        PS[Payment Service]
        LB[Load Balancer]
    end
    
    subgraph "Laravel Application"
        WC[WebhookController]
        QM[Queue Manager]
        WJ[ProcessPaymentWebhookJob]
        OM[Order Model]
        OEM[OrderEvent Model]
        WLM[WebhookLog Model]
    end
    
    subgraph "Data Layer"
        DB[(Database)]
        CACHE[(Redis Cache)]
    end
    
    PS -->|Webhook| LB
    LB -->|HTTP Request| WC
    WC -->|Rate Limit Check| CACHE
    WC -->|Duplicate Check| CACHE
    WC -->|Create Log| WLM
    WC -->|Dispatch Job| QM
    QM -->|Process| WJ
    WJ -->|Update Order| OM
    WJ -->|Create Event| OEM
    WJ -->|Update Log| WLM
    OM -->|Store| DB
    OEM -->|Store| DB
    WLM -->|Store| DB
                </div>
            </div>

        <!-- Webhook Processing Flow -->
                <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Webhook Processing Flow</h3>
                <div class="mermaid">
flowchart TD
    A[Webhook Received] --> B[Rate Limit Middleware]
    B --> C{Rate Limit OK?}
    C -->|No| D[Return 429 Too Many Requests]
    C -->|Yes| E[WebhookController.store]
    E --> F{Duplicate Check}
    F -->|Duplicate| G[Return 409 Conflict]
    F -->|New| H[Create WebhookLog]
    H --> I[Cache Transaction ID]
    I --> J[Dispatch Job to Queue]
    J --> K[Return 202 Accepted]
    
    K --> L[Job Processing Starts]
    L --> M{Order Exists?}
    M -->|No| N[Mark as Failed]
    M -->|Yes| O{Transaction Already Processed?}
    O -->|Yes| P[Mark as Ignored]
    O -->|No| Q{Valid Status Transition?}
    Q -->|No| R[Mark as Ignored]
    Q -->|Yes| S[Optimistic Lock Update]
    S --> T{Update Successful?}
    T -->|No| U[Retry with Backoff]
    T -->|Yes| V[Create OrderEvent]
    V --> W[Mark as Processed]
    U --> S
                </div>
            </div>

        <!-- Optimistic Locking Strategy -->
                <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Optimistic Locking Strategy</h3>
                <div class="mermaid">
sequenceDiagram
    participant W1 as Webhook Job 1
    participant W2 as Webhook Job 2
    participant DB as Database
    participant O as Order Record
    
    W1->>DB: SELECT order WHERE id = X
    W2->>DB: SELECT order WHERE id = X
    DB-->>W1: Order (updated_at: T1)
    DB-->>W2: Order (updated_at: T1)
    
    W1->>DB: UPDATE order SET status='paid', updated_at=NOW() WHERE id=X AND updated_at=T1
    W2->>DB: UPDATE order SET status='refunded', updated_at=NOW() WHERE id=X AND updated_at=T1
    
    DB-->>W1: 1 row affected (SUCCESS)
    DB-->>W2: 0 rows affected (FAILED)
    
    W1->>W1: Continue processing
    W2->>W2: Retry with new timestamp
                </div>
            </div>

        <!-- Rate Limiting Flow -->
                <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Rate Limiting Flow</h3>
                <div class="mermaid">
flowchart TD
    A[Webhook Request] --> B[Rate Limiting Middleware]
    B --> C{Check IP Rate Limit}
    C -->|Limit Exceeded| D[Return 429 Too Many Requests]
    C -->|Within Limit| E[Increment Counter]
    E --> F[Continue to Controller]
    F --> G[Duplicate Check]
    G --> H{Duplicate Found?}
    H -->|Yes| I[Return 409 Conflict]
    H -->|No| J[Process Webhook]
    J --> K[Cache Transaction ID]
    K --> L[Queue Job]
    L --> M[Return 202 Accepted]
    
    D --> N[Client Receives Rate Limit Info]
    N --> O[Client Waits & Retries]
    O --> A
                </div>
            </div>
        </section>

            <!-- 7. Advantages -->
            <section id="advantages" class="section-anchor mb-16">
                <h2 class="text-3xl font-bold text-gray-800 mb-6">🌟 Advantages</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold text-green-800 mb-4">🛡️ Reliability</h3>
                        <ul class="text-gray-700 space-y-2">
                            <li>• <strong>Idempotency:</strong> No duplicate processing</li>
                            <li>• <strong>Retry Logic:</strong> Automatic failure recovery</li>
                            <li>• <strong>Transaction Safety:</strong> ACID compliance</li>
                            <li>• <strong>Error Handling:</strong> Comprehensive exception management</li>
                            <li>• <strong>Health Checks:</strong> System monitoring endpoints</li>
                    </ul>
                </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold text-blue-800 mb-4">📈 Scalability</h3>
                        <ul class="text-gray-700 space-y-2">
                            <li>• <strong>Queue Processing:</strong> Asynchronous job handling</li>
                            <li>• <strong>Rate Limiting:</strong> Prevents system overload</li>
                            <li>• <strong>Database Indexing:</strong> Optimized queries</li>
                            <li>• <strong>Cache Strategy:</strong> Reduced database load</li>
                            <li>• <strong>Optimistic Locking:</strong> No blocking operations</li>
                        </ul>
                </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold text-purple-800 mb-4">🔧 Maintainability</h3>
                        <ul class="text-gray-700 space-y-2">
                            <li>• <strong>Clean Architecture:</strong> Separation of concerns</li>
                            <li>• <strong>Comprehensive Logging:</strong> Easy debugging</li>
                            <li>• <strong>Type Safety:</strong> Strong typing throughout</li>
                            <li>• <strong>Documentation:</strong> Well-documented code</li>
                            <li>• <strong>Testing:</strong> Comprehensive test coverage</li>
                        </ul>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-semibold text-orange-800 mb-4">👁️ Observability</h3>
                        <ul class="text-gray-700 space-y-2">
                            <li>• <strong>Audit Trail:</strong> Complete event history</li>
                            <li>• <strong>Performance Metrics:</strong> Processing time tracking</li>
                            <li>• <strong>Structured Logging:</strong> Searchable log entries</li>
                            <li>• <strong>Webhook Tracking:</strong> Full request/response logs</li>
                            <li>• <strong>Status Monitoring:</strong> Real-time system health</li>
                        </ul>
                </div>
            </div>
        </section>


            <!-- 8. Example Usage -->
            <section id="examples" class="section-anchor mb-16">
                <h2 class="text-3xl font-bold text-gray-800 mb-6">📝 Example Usage</h2>
                
                <!-- Webhook Payload Example -->
                <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Webhook Payload Example</h3>
                        <div class="code-block">
                        <div class="code-lang">JSON</div>
                        <button class="copy-btn" onclick="copyCode(this)">📋 Copy</button>
                        <pre><code>{
    <span class="json-key">"order_uuid"</span>: <span class="json-string">"550e8400-e29b-41d4-a716-446655440000"</span>,
    <span class="json-key">"txn_id"</span>: <span class="json-string">"TXN_123456789"</span>,
    <span class="json-key">"status"</span>: <span class="json-string">"paid"</span>,
    <span class="json-key">"amount"</span>: <span class="json-number">99.99</span>,
    <span class="json-key">"timestamp"</span>: <span class="json-string">"2024-01-15T10:30:00Z"</span>,
    <span class="json-key">"webhook_source"</span>: <span class="json-string">"payment_gateway"</span>,
    <span class="json-key">"correlation_id"</span>: <span class="json-string">"CORR_789"</span>,
    <span class="json-key">"metadata"</span>: {
        <span class="json-key">"payment_method"</span>: <span class="json-string">"credit_card"</span>,
        <span class="json-key">"card_last_four"</span>: <span class="json-string">"1234"</span>,
        <span class="json-key">"currency"</span>: <span class="json-string">"USD"</span>,
        <span class="json-key">"customer_id"</span>: <span class="json-string">"CUST_456"</span>
    }
}</code></pre>
                    </div>
                    </div>
                    
                <!-- CURL Example -->
                <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">API Call Example (CURL)</h3>
                        <div class="code-block">
                        <div class="code-lang">BASH</div>
                        <button class="copy-btn" onclick="copyCode(this)">📋 Copy</button>
                        <pre><code><span class="php-function">curl</span> -X POST <span class="php-string">http://localhost:8000/api/webhooks/payment</span> \
  -H <span class="php-string">"Content-Type: application/json"</span> \
  -H <span class="php-string">"Authorization: Bearer YOUR_API_TOKEN"</span> \
  -d <span class="php-string">'{
    "order_uuid": "550e8400-e29b-41d4-a716-446655440000",
    "txn_id": "TXN_123456789",
    "status": "paid",
    "amount": 99.99,
    "timestamp": "2024-01-15T10:30:00Z",
    "webhook_source": "payment_gateway",
    "correlation_id": "CORR_789",
    "metadata": {
        "payment_method": "credit_card",
        "card_last_four": "1234",
        "currency": "USD",
        "customer_id": "CUST_456"
    }
}'</span></code></pre>
                    </div>
                </div>
                
                <!-- Response Example -->
                <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Success Response</h3>
                    <div class="code-block">
<pre><code>{
    "message": "Webhook received and queued for processing",
    "webhook_log_id": 12345,
    "txn_id": "TXN_123456789",
    "status": "queued",
    "processing_time_ms": 15.23
}</code></pre>
                </div>
            </div>
        </section>

            <!-- 9. Quick Start -->
            <section id="quickstart" class="section-anchor mb-16">
                <h2 class="text-3xl font-bold text-gray-800 mb-6">🚀 Quick Start</h2>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="space-y-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">1. Install Dependencies</h3>
                            <div class="code-block">
<pre><code>composer install</code></pre>
                        </div>
                    </div>
                    
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">2. Environment Setup</h3>
                            <div class="code-block">
<pre><code>cp .env.example .env
php artisan key:generate</code></pre>
                                </div>
                            <p class="text-sm text-gray-600 mt-2">
                                Configure your database connection and other environment variables in the <code>.env</code> file.
                    </p>
                </div>
                
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">3. Database Setup</h3>
                            <div class="code-block">
<pre><code>php artisan migrate:fresh --seed</code></pre>
                        </div>
                            <p class="text-sm text-gray-600 mt-2">
                                This will create all necessary tables and populate them with sample data.
                            </p>
                    </div>
                    
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">4. Start Queue Worker</h3>
                        <div class="code-block">
<pre><code>php artisan queue:work --queue=webhooks-high-priority</code></pre>
                        </div>
                            <p class="text-sm text-gray-600 mt-2">
                                Start the queue worker to process webhook jobs. Run this in a separate terminal.
                        </p>
                    </div>
                    
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">5. Run Tests</h3>
                        <div class="code-block">
<pre><code>php artisan test</code></pre>
                        </div>
                            <p class="text-sm text-gray-600 mt-2">
                                Verify that all tests pass to ensure the system is working correctly.
                            </p>
                    </div>
                    
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">6. Start Development Server</h3>
                        <div class="code-block">
<pre><code>php artisan serve</code></pre>
                        </div>
                            <p class="text-sm text-gray-600 mt-2">
                                The application will be available at <code>http://localhost:8000</code>
                            </p>
                    </div>
                </div>
            </div>
        </section>

    <!-- Footer -->
            <footer class="mt-16 pt-8 border-t border-gray-200">
                <div class="text-center text-gray-600 mb-6">
                    <p class="text-lg font-semibold text-gray-800 mb-2">Order Management + Webhooks System</p>
                   
                </div>
                
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-6 border border-gray-200">
                <div class="text-center">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Thank You ...</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div class="flex items-center justify-center space-x-2">
                                <span class="text-gray-500">📧</span>
                                <span class="text-gray-700">ahmed.maksoudaa@gmail.com</span>
                    </div>
                            <div class="flex items-center justify-center space-x-2">
                                <span class="text-gray-500">📱</span>
                                <span class="text-gray-700">+201090084443</span>
                </div>
                            <div class="flex items-center justify-center space-x-2">
                                <span class="text-gray-500">👤</span>
                                <span class="text-gray-700">Ahmed Ayman</span>
                            </div>
                    </div>
                </div>
            </div>
            
                <div class="text-center mt-6 text-xs text-gray-500">
                    <p class="mt-2 font-semibold text-gray-600">© 2025 Ahmed Ayman • All rights reserved</p>
        </div>
    </footer>
        </div>
    </main>

    <script>
        // Initialize Mermaid
        mermaid.initialize({ startOnLoad: true });
        
        // Copy code functionality
        function copyCode(button) {
            const codeBlock = button.parentElement;
            const codeElement = codeBlock.querySelector('code');
            const text = codeElement.textContent;
            
            navigator.clipboard.writeText(text).then(() => {
                    // Show success feedback
                const originalText = button.innerHTML;
                button.innerHTML = '✅ Copied!';
                button.classList.add('copied');
                    
                    // Reset after 2 seconds
                    setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('copied');
                    }, 2000);
            }).catch(err => {
                    // Fallback for older browsers
                    const textArea = document.createElement('textarea');
                textArea.value = text;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                    
                    // Show success feedback
                const originalText = button.innerHTML;
                button.innerHTML = '✅ Copied!';
                button.classList.add('copied');
                    
                    setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('copied');
                    }, 2000);
            });
        }
        
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Highlight active navigation item
        window.addEventListener('scroll', function() {
            const sections = document.querySelectorAll('section[id]');
            const navLinks = document.querySelectorAll('.nav-link');
            
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (scrollY >= (sectionTop - 200)) {
                    current = section.getAttribute('id');
                }
            });
            
            navLinks.forEach(link => {
                link.classList.remove('bg-blue-100', 'text-blue-800');
                if (link.getAttribute('href') === '#' + current) {
                    link.classList.add('bg-blue-100', 'text-blue-800');
                }
            });
        });
        
        // Add copy buttons to all code blocks that don't have them
        document.addEventListener('DOMContentLoaded', function() {
            const codeBlocks = document.querySelectorAll('.code-block');
            codeBlocks.forEach(block => {
                if (!block.querySelector('.copy-btn')) {
                    const copyBtn = document.createElement('button');
                    copyBtn.className = 'copy-btn';
                    copyBtn.innerHTML = '📋 Copy';
                    copyBtn.onclick = () => copyCode(copyBtn);
                    block.appendChild(copyBtn);
                }
            });
        });
    </script>
</body>
</html>
