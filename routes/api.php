<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GatewayPaymentController;


Route::post('gateway-payments/create', [GatewayPaymentController::class, 'create'])
    ->name('gateway-payments.create');

Route::post('gateway-payments/finally', [GatewayPaymentController::class, 'finally'])
    ->name('gateway-payments.finally');