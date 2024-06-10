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
        $response = [];
        $status = 400;

        $dataResponse = [];
        $dataResponse['data'] = $request->all();

        try {
            $paymentGateway = app()->make(PaymentGatewayInterface::class, ['gateway' => 'ASASS']);
            $response = $paymentGateway->finallyPayment($request->all()); 
            
            $dataResponse['data']['encodedImage'] = empty($response['data']->encodedImage) === false ? $response['data']->encodedImage : '';
            $dataResponse['data']['expirationDate'] = empty($response['data']->expirationDate) === false ? $response['data']->expirationDate : '';
            $dataResponse['data']['payload'] = empty($response['data']->payload) === false ? $response['data']->payload : '';

        } catch (\Exception $e) {
            $response['errors'] = $e->getMessage();
            $response['message'] = 'Erro para finalizar a cobrança!';

            return response()->json($dataResponse, $status);
        }      

        return Inertia::render('Payments/Result', $response);
        
    }

}
