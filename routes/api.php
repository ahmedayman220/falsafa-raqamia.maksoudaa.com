<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/webhooks/payments', [WebhookController::class, 'store'])
    ->middleware('webhook.rate.limit:1000,1');

Route::get('/orders', [OrderController::class, 'index']);
Route::get('/orders/{uuid}', [OrderController::class, 'show']);
Route::get('/orders/{uuid}/events', [OrderController::class, 'events']);