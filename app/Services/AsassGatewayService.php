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
    protected $method = 'GETd';
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
     * Faz requisição no gateway de pagamento para criar uma cobrança.
     *
     * @param  array  $body
     * @return array
     */
    public function createPayment(array $body): array
    {       
        try {
            $body['customer'] = $this->getCustomer($body);

            $today = Carbon::now();
            $body['dueDate'] = $today->addDays(15);

            $body = $this->handleSendPayment($body);
            $processBody = $this->send($body);            

            $processBodyResource = new AsassPaymentResource((object) $processBody);
            
            $this->response['data'] = $processBodyResource;       
            $this->response['message'] = 'Seu pedido foi processado com sucesso. Clique no botão abaixo para acessar e concluir o pagamento.';
            $this->response['status'] = 200;
            $this->response['success'] = true;

        } catch (\Exception $e) {
            $this->response['error'] = $e->getMessage();
            $this->response['message'] = "Desculpe, encontramos um erro inesperado para processar este pagamento. \n\nPor favor tente novamente mais tarde e tenha certeza de que os dados informados estão corretos.";
        }

        return $this->response;
    }

    /**
     * Faz requisição no gateway de pagamento para finalizar uma cobrança.
     *
     * @param  array  $body
     * @return array
     */
    public function finallyPayment(array $body): array
    {       
        try {
            if ($body['billingType'] === BillingTypeEnum::BOLETO->name) {
                throw new \Exception('Não permitido finalizar cobrança para este meio pagamento.');   
            }  

            $body = $this->handleSendPayment($body);

            switch ($body['billingType']) {
                case BillingTypeEnum::CREDIT_CARD->name:
                    $this->method = 'POST';
                    $this->ApiUrl = $this->ApiUrl . '/' . $body['billingId'] . '/payWithCreditCard';
                    break;

                case BillingTypeEnum::PIX->name:
                    $this->method = 'GET';
                    $this->ApiUrl = $this->ApiUrl . '/' . $body['billingId'] . '/pixQrCode';
                    break;   
            }  
            
            $processBody = $this->send($body);

            $processBody = $this->handleResponse($body, $processBody);
            
            $processBodyResource = new AsassPaymentResource((object) $processBody); 

            $this->response['data'] = $processBodyResource;       
            $this->response['message'] = 'Conclua o pagamento :';
            $this->response['status'] = 200;

        } catch (\Exception $e) {
            $this->response['data'] = [];
            $this->response['data']['data'] = $body;
            $this->response['errors'] = $e->getMessage();
            $this->response['message'] = 'Erro para finalizar a cobrança! Clique no link de cobrança abaixo para realizar o pagamento!';
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
            $data['name'] = $body['name'];  
            $data['cpfCnpj'] = $body['cpfCnpj']; 
            
            $this->ApiUrl = $this->ApiUrlCustomer;
            $this->method = 'POST';

            $processBody = $this->send($data);
            
            if (empty($processBody['id']) === true) {
                throw new \Exception('Erro ao cadastrar novo customer');         
            }

            $customerId = $processBody['id'];

            $user->customer = $customerId;
            $user->save();
        }

        $this->response['user'] = $user;

        return $customerId;
    }

    /**
     * Envia requisição para o gateway de pagamento.
     *
     * @param  array  $body
     * @return array
     */
    public function send($body): array 
    {      
        if (
            empty($this->apiToken) === true 
            || in_array($this->method, array('POST', 'GET')) === false 
            || empty($this->ApiUrl) === true
        ) { 
            throw new \Exception('Dados de envio estao incorretos');
        }

        $headers = [
            'headers' => [
                'accept' => 'application/json',
                'access_token' => $this->apiToken,
                'content-type' => 'application/json',
            ],
        ];

        if ($this->method === 'POST') $headers['body'] = json_encode($body);

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
     * Trata os dados de pagamento antes de enviar.
     *
     * @param  array $body    Dados para envio.
     * @return array
     */
    public function handleSendPayment(array $body): array
    {
        $this->ApiUrl = env('BILL_API_URL');

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
            $this->method = 'POST';
        } elseif ($body['billingType'] === BillingTypeEnum::BOLETO->name) {
            $this->method = 'POST';
        }

        return $body;
    }

    /**
     * Trata os dados recebidos.
     *
     * @param  array $body      Dados para envio.
     * @param  array $response  Dados recebidos.
     * @return array
     */
    public function handleResponse($body, $response): array
    {
        switch ($body['billingType']) {
            case BillingTypeEnum::PIX->name:
                $responseNew = $body;
                $responseNew['encodedImage'] = $response['encodedImage'];
                $responseNew['payload'] = $response['payload'];
                $responseNew['expirationDate'] = $response['expirationDate'];
                $body = $responseNew;
                break;   
        }

        return $body;
    }
}
