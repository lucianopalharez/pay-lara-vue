<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GatewayPaymentController;


Route::post('gateway-payments/generate', [GatewayPaymentController::class, 'generate'])
    ->name('gateway-payments.generate');