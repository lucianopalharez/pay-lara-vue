<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request as RequestCustom;
use App\Http\Requests\PaymentRequest;
use Illuminate\Http\JsonResponse;
use Inertia\Response as InertiaResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Request;
use App\Enums\BillingTypeEnum;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Symfony\Component\DomCrawler\Crawler;
use App\Http\Requests\GatewayPaymentRequest;

class PaymentController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('Payments/Index', [
            'filters' => Request::all('search'),
            'payments' => \Auth::user()->payments()
                ->orderBy('created_at','desc')
                ->filter(Request::only('search'))
                ->paginate(10)
                ->withQueryString()
                ->through(fn ($payment) => [
                    'id' => $payment->id,
                    'user' => $payment->user->first_name,
                    'billingType' => $payment->billingType,
                    'value' => $payment->value,
                    'created' => $payment->created_at_formatted,
                    'dueDate' => $payment->dueDate,
                    'status' => $payment->status,
                    'invoiceNumber' => $payment->invoiceNumber,
                    'invoiceUrl' => $payment->invoiceUrl,
                ]),
        ]);
    }

    /**
     * Gera pagamento e salva no banco de dados.
     *
     * @param  PaymentRequest  $request
     * @param  GatewayAsaasController $gateway
     * @return InertiaResponse
     */
    public function store(PaymentRequest $request)
    {
        try {
            $request->validated();
            
            DB::beginTransaction();
   
            $payment = \Auth::user()->payments()->create($request->except('dueDateFormated','expirationDate','id'));            

            DB::commit();

            return response()->json(['id' => $payment->id], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 402);
        }  
        
    }

    public function create()
    {
        return Inertia::render('Payments/Create', [
            'billingTypes' => BillingTypeEnum::values(),
            'user' => \Auth::user(),
        ]);
    }  
    
    /**
     * Valida o formulario de pagamento.
     *
     * @param  GatewayPaymentRequest  $request
     * @return InertiaResponse
     */
    public function validatePayment(GatewayPaymentRequest $request): InertiaResponse
    {
        $response = [
            'billingTypes' => BillingTypeEnum::values(),
            'user' => \Auth::user(),
            'errors' => [],
            'input' => $request->all(),
            'success' => true
        ];

        try {
            $request->validated();
        } catch (ValidationException $e) {
            $response['success'] = false;         
            $response['errors'] = array_map(function($item) {
                return $item[0];
            }, $e->errors());
        }      

        return Inertia::render('Payments/Create', $response);
    }

    /**
     * Carrega resultado de pagamento.
     *
     * @param  RequestCustom  $request
     * @return InertiaResponse
     */
    public function resultPayment(Payment $payment, RequestCustom $request): InertiaResponse
    {
        $response = [
            'user' => \Auth::user(),
            'errors' => [],
            'message' => $request->message,
            'data' => $request->all(),
            'success' => $request->success
        ];
        
        $response['data']['data']['encodedImage'] = $payment->encodedImage;
        $response['data']['data']['payload'] = $payment->payload;

        return Inertia::render('Payments/Result', $response);
    }

}
