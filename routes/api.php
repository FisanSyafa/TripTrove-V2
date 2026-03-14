<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaymentCallbackController; // <--- Import Controller

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Tambahkan Route Midtrans di sini
Route::post('/midtrans-callback', [PaymentCallbackController::class, 'handle']);