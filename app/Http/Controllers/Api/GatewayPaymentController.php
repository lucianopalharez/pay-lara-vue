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
    /**
     * Gera pagamento no gateway.
     *
     * @param  GatewayPaymentRequest  $request
     * @return InertiaResponse
     */
    public function create(GatewayPaymentRequest $request): InertiaResponse
    {
        $response = [];
        $page = 'Payments/Result';

        try {
            $request->validated();

            $paymentGateway = app()->make(PaymentGatewayInterface::class, ['gateway' => 'ASASS']);
            $response = $paymentGateway->createPayment($request->all());

        } catch (ValidationException $e) {
            
            $errors = array_map(function($item) {
                return $item[0];
            }, $e->errors());

            $page = 'Payments/Create';
            $response = [
                'billingTypes' => BillingTypeEnum::values(),
                'user' => \Auth::user(),
                'errors' => $errors,
                'input' => $request->all(),
            ];
        }      

        return Inertia::render($page, $response);
    }

    /**
     * Finaliza o pagamento no gateway.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function finally(Request $request)
    {
        
        $paymentGateway = app()->make(PaymentGatewayInterface::class, ['gateway' => 'ASASS']);
        $response = $paymentGateway->finallyPayment($request->all()); 

        return Inertia::render('Payments/Result', $response);
        
    }

}
