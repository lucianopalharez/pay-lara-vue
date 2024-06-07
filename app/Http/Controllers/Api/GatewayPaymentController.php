<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\GatewayPaymentRequest;
use App\Contracts\PaymentGatewayInterface;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class GatewayPaymentController extends Controller
{
    /**
     * Gera pagamento no gateway.
     *
     * @param  GatewayPaymentRequest  $request
     * @return InertiaResponse
     */
    public function generate(GatewayPaymentRequest $request): InertiaResponse
    {
        $request->validated();

        $paymentGateway = app()->make(PaymentGatewayInterface::class, ['billingType' => $request['billingType']]);
        $response = $paymentGateway->process($request->all());

        return Inertia::render('Payments/Result', $response);
    }
}
