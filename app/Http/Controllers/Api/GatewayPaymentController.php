<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\GatewayPaymentRequest;
use App\Contracts\PaymentGatewayInterface;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Validation\ValidationException;
use App\Enums\BillingTypeEnum;
use App\Services\AsassGatewayService;
use Illuminate\Http\JsonResponse;

class GatewayPaymentController extends Controller
{
    public $gateway = 'ASASS';

    /**
     * Cria uma cobrança no gateway.
     *
     * @param  GatewayPaymentRequest  $request
     * @return JsonResponse
     */
    public function create(GatewayPaymentRequest $request): JsonResponse
    {
        try {
            $paymentGateway = app()->make(PaymentGatewayInterface::class, ['gateway' => $this->gateway]);
            $response = $paymentGateway->createPayment($request->all());

            return response()->json($response, 200);
        } catch (\Exception $e) {            
            return response()->json(['error' => $e->getMessage()], 402);
        }    
    }

    /**
     * Finaliza a cobrança no gateway.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function finally(Request $request)
    {        
        try {
            $paymentGateway = app()->make(PaymentGatewayInterface::class, ['gateway' => $this->gateway]);
            $response = $paymentGateway->finallyPayment($request->all()); 

            return response()->json($response, 200);
        } catch (\Exception $e) {            
            return response()->json(['error' => $e->getMessage()], 402);
        }    
    }

}
