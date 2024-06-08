<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Carbon\Carbon;
use App\Enums\BillingTypeEnum;
use App\Http\Resources\CreditCardPaymentResource;
use App\Services\GatewayService;
use GuzzleHttp\Psr7\Uri;
use App\Models\User;

class CreditCardGatewayService extends GatewayService implements PaymentGatewayInterface
{

    /**
     * Faz requisição no gateway de pagamento para gerar pagamento.
     *
     * @param  array  $body
     * @return array
     */
    public function process(array $body): array
    {       
        $response = [
            'success' => false,
            'status' => 401,
            'data' => null,
            'message' => '',
            'user' => null
        ];

        try {
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

            $body['remoteIp'] = '154.485.548.58';

            $response['user'] = User::find($body['userId']);       
            $body['customer'] = $this->getCustomer($response['user']->customer);

            $today = Carbon::now();
            $body['dueDate'] = $today->addDays(15);

            $url = new Uri($this->ApiUrl);

            $process = $this->http->request(
                'POST', 
                $url, 
                [
                    'body' => json_encode($body),
                    'headers' => [
                        'accept' => 'application/json',
                        'access_token' => $this->apiToken,
                        'content-type' => 'application/json',
                    ],
                ]
            );

            $processBody = json_decode((string) $process->getBody(), true);

            $processBodyResource = new CreditCardPaymentResource((object) $processBody);            

            $response['data'] = $processBodyResource;            
            $response['success'] = true;
            $response['status'] = $process->getStatusCode();
            $response['message'] = 'Seu pedido foi processado com sucesso. Clique no botão abaixo para acessar o boleto e concluir o pagamento.';
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            dd($e);
            if ($e->hasResponse()) {
                $response['status'] = $e->getResponse()->getStatusCode();
                $response['message'] = $e;
            } else {
                $response['message'] = "Erro desconhecido!";
            }
        }

        return $response;
    }
}
