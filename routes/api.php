<?php

use App\Http\Controllers\Api\MidtransWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/midtrans/notification', MidtransWebhookController::class)
    ->name('midtrans.notification');
