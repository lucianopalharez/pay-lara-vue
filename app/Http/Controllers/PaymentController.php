<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request as RequestCustom;
use App\Http\Requests\PaymentRequest;
use App\Contracts\PaymentGatewayInterface;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\PaymentResource;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Request;
use App\Enums\BillingTypeEnum;

class PaymentController extends Controller
{
    public function index(): Response
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

    public function store(PaymentRequest $request): JsonResponse
    {
        if ($request->fails()) {
            // Há erros de validação
            return response()->json($request->errors(), 400); // Retorna os erros em JSON
        }
        
        return response()->json(['teste']);

        try {
            //code...
        } catch (\Exception $e) {
            //throw $th;
        }
        $data = $request->validated();

        $gatewayType = $request['type'];

        $paymentGateway = app()->make(PaymentGatewayInterface::class, ['type' => $gatewayType]);
        $paymentGateway->process($request->all());

        return response()->json(new PaymentResource((object)$response));
    }

    public function create(): Response
    {
        return Inertia::render('Payments/Create', [
            'billingTypes' => BillingTypeEnum::values(),
        ]);
    }

}
