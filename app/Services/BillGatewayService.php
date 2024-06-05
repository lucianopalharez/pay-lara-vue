<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

class BillGatewayService implements PaymentGatewayInterface
{
    protected $ApiUrl;
    protected $apiToken;

    public function __construct()
    {
        $this->ApiUrl = env('BILL_API_URL');
        $this->apiToken = env('API_TOKEN');
    }

    public function process(array $data)
    {
        //print_r(json_encode($data));exit;
        try {

            $client = new \GuzzleHttp\Client();

            $data['customer'] = $this->getCustomer();
            $data['dueDate'] = '2024-06-05';

            $response = $client->request('POST', $this->ApiUrl, [
            'body' => json_encode($data),
            'headers' => [
                'accept' => 'application/json',
                'access_token' => $this->apiToken,
                'content-type' => 'application/json',
            ],
            ]);
        
            return json_decode((string) $response->getBody(), true);

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getCustomer(): string 
    {
        $customerId = \Auth::user()->customer;

        if (empty($customerId) === true) {
            
        }

        return $customerId;
    }
}
