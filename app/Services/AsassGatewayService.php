<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Enums\BillingTypeEnum;
use App\Models\User;
use App\Http\Resources\AsassPaymentResource;

class AsassGatewayService implements PaymentGatewayInterface
{
    protected $ApiUrl;
    protected $apiToken;
    protected $http;
    protected $pixAddressKey;
    protected $method;
    protected $finally = [
        BillingTypeEnum::CREDIT_CARD->name => ['url' => 'payWithCreditCard','status' => true],
        BillingTypeEnum::PIX->name => ['url' => 'pixQrCode','status' => true],
        BillingTypeEnum::BOLETO->name => ['url' => '','status' => false],
    ];
    protected $response = [
        'success' => false,
        'status' => 400,
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
     * Faz requisição no gateway de pagamento para criar um pagamento.
     *
     * @param  array  $body
     * @return array
     */
    public function createPayment(array $body): array
    {       
        try {
            $this->method = 'POST';

            $body['customer'] = $this->getCustomer($body);

            $today = Carbon::now();
            $body['dueDate'] = $today->addDays(15);

            $body = $this->handleSend($body);
            $processBody = $this->send($body); 


            $processBody = $this->handleResponse($processBody);
            

            $processBodyResource = new AsassPaymentResource((object) $processBody);
            
           

            $this->response['data'] = $processBodyResource;       
            $this->response['message'] = 'Seu pedido foi processado com sucesso. Clique no botão abaixo para acessar e concluir o pagamento.';
            $this->response['status'] = 200;
            $this->response['success'] = true;

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $this->response['error'] = $e;
            $this->response['message'] = "Desculpe, encontramos um erro inesperado para processar este pagamento. \n\nPor favor tente novamente mais tarde e tenha certeza de que os dados informados estão corretos.";
        }

        return $this->response;
    }

    /**
     * Faz requisição no gateway de pagamento para finalizar um pagamento.
     *
     * @param  array  $body
     * @return array
     */
    public function finallyPayment(array $body): array
    {       
        try {            
            if ($this->finally[$body['billingType']]['status'] === false) {
                $this->response['message'] = 'Não permitido finalizar cobrança para este meio pagamento.';

                return $this->response;
            }            

            $body = $this->handleSend($body);
            $processBody = $this->send($body);         

            $processBodyResource = new AsassPaymentResource((object) $processBody);    

            $this->response['data'] = $processBodyResource;       
            $this->response['message'] = 'Seu pagamento foi finalizado.';
            $this->response['status'] = 200;

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $this->response['error'] = $e;
            $this->response['message'] = "Encontramos um erro inesperado para finalizar este pagamento.";
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
     * Envia requisição para o gateway de pagamento.
     *
     * @param  array $body
     * @return array
     */
    public function send($body): array 
    {         
        $headers = [
            'headers' => [
                'accept' => 'application/json',
                'access_token' => $this->apiToken,
                'content-type' => 'application/json',
            ],
        ];

        if ($this->method == 'POST') $headers['body'] = json_encode($body);

        $process = $this->http->request(
            $this->method, 
            $this->ApiUrl, 
            $headers
        );

        $this->response['success'] = true;
        $this->response['status'] = $process->getStatusCode();

        return json_decode((string) $process->getBody(), true);
    }

    /**
     * Trata os dados antes de enviar e define o metodo de envio.
     *
     * @param  array $body    Dados para envio.
     * @return array
     */
    public function handleSend(array $body): array
    {
        if ($body['billingType'] === BillingTypeEnum::CREDIT_CARD->name) {
            $this->method = 'POST';

            if (empty($body['billingId']) === true) $body['billingType'] = 'UNDEFINED';

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
        } elseif ($body['billingType'] === BillingTypeEnum::PIX->name) {
            $this->method = 'GET';
        } elseif ($body['billingType'] === BillingTypeEnum::BOLETO->name) {
            $this->method = 'POST';
        }

        return $body;
    }

    /**
     * Trata a resposta da requisição.
     *
     * @param  array $body    Dados da resposta.
     * @return array
     */
    public function handleResponse(array $body): array
    {
        if (empty($body['data']) === false) {
            if (is_array($body['data']) === true) {
                $body = end($body['data']);
            } else {
                $body = $body['data'];
            }
        }
        
        return $body;
    }
}
