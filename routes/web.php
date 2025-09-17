<?php

use App\Http\Controllers\Web\DocumentationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DocumentationController::class, 'index']);
Route::get('/documentation', [DocumentationController::class, 'index']);
