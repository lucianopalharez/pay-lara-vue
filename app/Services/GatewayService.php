<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Carbon\Carbon;
use App\Enums\BillingTypeEnum;
use App\Http\Resources\PaymentResource;

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
     * @return string
     */
    public function getCustomer(): string 
    {
        $customerId = \Auth::user()->customer;        
        
        if (empty($customerId) === true) {

        }

        return $customerId;
    }

    /**
     * Corelaciona as colunas da tabela payment do banco com as colunas do pagamento gerado do asass.
     *
     * @param  array  $data
     * @return array
     */
    public function prepareResponseToColumnsTable(array $data): array
    {
        return [
            'invoiceNumber' => $data['invoiceNumber'],
            'bankSlipUrl' => $data['bankSlipUrl'],
            'invoiceUrl' => $data['invoiceUrl'],
            'externalReference' => $data['externalReference'],
            'description' => $data['description'],
            'status' => $data['status'],
            'pixTransaction' => $data['pixTransaction'],
            'canBePaidAfterDueDate' => $data['canBePaidAfterDueDate'],
            'billingType' => $data['billingType'],
            'value' => $data['value'],
            'dueDate' => $data['dueDate'],
            'paymentCreated' => $data['dateCreated']
        ];
    }
}
