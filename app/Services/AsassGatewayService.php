<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Enums\BillingTypeEnum;
use App\Models\User;
use App\Http\Resources\AsassPaymentResource;

class AsassGatewayService
{
    protected $ApiUrl;
    protected $apiToken;
    protected $http;
    protected $pixAddressKey;

    protected $response = [
        'success' => false,
        'status' => 401,
        'data' => null,
        'message' => '',
        'user' => null
    ];

    public function __construct()
    {
        $this->ApiUrl = env('BILL_API_URL');
        $this->ApiUrlCustomer = env('CUSTOMER_API_URL');
        $this->pixAddressKey = env('PIX_API_URL');
        $this->apiToken = env('API_TOKEN');
        $this->pixAddressKey = env('PIX_ADDRESS');
        $this->http = new \GuzzleHttp\Client();
    }

    /**
     * Faz requisição no gateway de pagamento para gerar pagamento.
     *
     * @param  array  $body
     * @return array
     */
    public function process(array $body): array
    {       
        try {
            $body = $this->handleSend($body);
            $processBody = $this->send($body);         

            $processBodyResource = new AsassPaymentResource((object) $processBody);    

            $this->response['data'] = $processBodyResource;       
            $this->response['message'] = 'Seu pedido foi processado com sucesso. Clique no botão abaixo para acessar e concluir o pagamento.';

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                $this->response['status'] = $e->getResponse()->getStatusCode();
                $this->response['message'] = "Erro inesperado!";
            } else {
                $this->response['message'] = "Erro desconhecido!";
            }
        }

        return $this->response;
    }

    /**
     * Pega codigo do cliente do gateway de pagamento.
     *
     * @param  array $body
     * @return string
     */
    public function getCustomer($body): string 
    {          
        $user = User::find($body['userId']);
        $customerId = $user->customer;

        if (empty($customerId) === true) {
            
            $data = [];
            $data['nome'] = $body['nome'];  
            $data['cpfCnpj'] = $body['cpfCnpj'];  

            $process = $this->http->request(
                'POST', 
                $this->ApiUrlCustomer, 
                [
                    'body' => json_encode($data),
                    'headers' => [
                        'accept' => 'application/json',
                        'access_token' => $this->apiToken,
                        'content-type' => 'application/json',
                    ],
                ]
            );

            $processBody = json_decode((string) $process->getBody(), true);
            $customerId = $processBody->id;

            $user->customer = $customerId;
            $user->save();
        }

        $this->response['user'] = $user;

        return $customerId;
    }

    /**
     * Envia requisição para criar pagamento.
     *
     * @param  array $body
     * @return array
     */
    public function send($body): array 
    {         
        $body['customer'] = $this->getCustomer($body);

        $today = Carbon::now();
        $body['dueDate'] = $today->addDays(15);

        $process = $this->http->request(
            'POST', 
            $this->ApiUrl, 
            [
                'body' => json_encode($body),
                'headers' => [
                    'accept' => 'application/json',
                    'access_token' => $this->apiToken,
                    'content-type' => 'application/json',
                ],
            ]
        );

        $this->response['success'] = true;
        $this->response['status'] = $process->getStatusCode();

        return json_decode((string) $process->getBody(), true);
    }

    /**
     * Trata os dados antes de enviar.
     *
     * @param  array $body
     * @return array
     */
    private function handleSend($body): array
    {
        if ($body['billingType'] == BillingTypeEnum::CREDIT_CARD) {
            $body['billingType'] = 'UNDEFINED';

            $body['creditCard'] = [
                'holderName' => $body['name'],
                'number' => $body['creditCardNumber'],
                'expiryMonth' => $body['expiryMonth'],
                'expireYear' => $body['expiryYear'],
                'cvv' => $body['cvv']                
            ];

            $body['creditCardHolderInfo'] = [
                'name' => $body['name'],
                'email' => $body['email'],
                'cpfCnpj' => $body['cpfCnpj'],
                'portalCode' => $body['postalCode'],
                'addressNumber' => $body['addressNumber'],
                'phone' => $body['phone']                                 
            ];

            $body['remoteIp'] = $body['ip'];
        }

        return $body;
    }

}