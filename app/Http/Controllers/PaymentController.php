<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request as RequestCustom;
use App\Http\Requests\PaymentRequest;
use App\Contracts\PaymentGatewayInterface;
use Illuminate\Http\JsonResponse;
use Inertia\Response as InertiaResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Request;
use App\Enums\BillingTypeEnum;

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

    public function store(PaymentRequest $request): InertiaResponse
    {
        $request->validated();

        $paymentGateway = app()->make(PaymentGatewayInterface::class, ['billingType' => $request['billingType']]);
        $response = $paymentGateway->process($request->all());

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
