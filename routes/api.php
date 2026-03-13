<?php

use App\Http\Controllers\Api\WazuhWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/wazuh/webhook', [WazuhWebhookController::class, 'receive']);
