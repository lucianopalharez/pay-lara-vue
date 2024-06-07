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
use App\Http\Controllers\Api\GatewayAsaasController;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('Payments/Index', [
            'filters' => Request::all('search'),
            'payments' => \Auth::user()->payments()
                ->orderBy('paymentCreated','desc')
                ->filter(Request::only('search'))
                ->paginate(10)
                ->withQueryString()
                ->through(fn ($payment) => [
                    'id' => $payment->id,
                    'user' => $payment->user->first_name,
                    'billingType' => $payment->billingType,
                    'value' => $payment->value,
                    'paymentCreated' => $payment->paymentCreated,
                    'dueDate' => $payment->dueDate,
                    'status' => $payment->status
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
    public function store(PaymentRequest $request, GatewayAsaasController $apiGateway): InertiaResponse
    {
        $request->validated();

        $response = $apiGateway->generatePayment($request->all());
        $canSaveDB = $response['success'];

        if ($canSaveDB === true) {
            try {
                DB::beginTransaction();

                \Auth::user()->payments()->create($response['dataToSave']);               

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
            }
        }

        return Inertia::render('Payments/Result', $response);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Payments/Create', [
            'billingTypes' => BillingTypeEnum::values(),
            'user' => \Auth::user()
        ]);
    }

}