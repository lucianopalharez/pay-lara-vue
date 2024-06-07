<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Contracts\PaymentGatewayInterface;

class GatewayAsaasController extends Controller
{
    /**
     * Gera pagamento no gateway asass.
     *
     * @param  array  $request
     * @return array
     */
    public function generatePayment(array $request): array
    {        
        $paymentGateway = app()->make(PaymentGatewayInterface::class, ['billingType' => $request['billingType']]);
        return $paymentGateway->process($request);
    }

}
