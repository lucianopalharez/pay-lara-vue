<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;

class BillGatewayService implements PaymentGatewayInterface
{
    protected $ApiUrl;
    protected $apiToken;

    public function __construct()
    {
        $this->ApiUrl = env('BILL_API_URL');
        $this->apiToken = env('API_TOKEN');
    }

    public function generatePayment(array $data)
    {
        $response = Http::withToken($this->apiToken)->post($this->ApiUrl, $data);
        return $response->json();
    }
}
