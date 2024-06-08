<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Carbon\Carbon;
use App\Enums\BillingTypeEnum;

class GatewayService
{
    protected $ApiUrl;
    protected $apiToken;
    protected $http;

    public function __construct()
    {
        $this->ApiUrl = env('BILL_API_URL');
        $this->apiToken = env('API_TOKEN');
        $this->http = new \GuzzleHttp\Client();
    }

    /**
     * Pega codigo do cliente do gateway de pagamento.
     *
     * @param  null $customerId
     * @return string
     */
    public function getCustomer($customerId = null): string 
    {               
        if (empty($customerId) === true) {

        }

        return $customerId;
    }
}
