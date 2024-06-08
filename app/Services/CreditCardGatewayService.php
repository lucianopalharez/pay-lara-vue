<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use App\Enums\BillingTypeEnum;
use App\Http\Resources\PaymentResource;
use App\Services\GatewayService;

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

            $body['remoteIp'] = $body['ip'];

            $processBody = $this->send($body);

            $processBodyResource = new PaymentResource((object) $processBody);            

            $this->response['data'] = $processBodyResource;          
            $this->response['message'] = 'Seu pedido foi processado com sucesso. Clique no botão abaixo para acessar o boleto e concluir o pagamento.';

        } catch (\GuzzleHttp\Exception\RequestException $e) {        
            if ($e->hasResponse()) {
                $this->response['status'] = $e->getResponse()->getStatusCode();
                $this->response['message'] = $e;
            } else {
                $this->response['message'] = "Erro desconhecido!";
            }
        }

        return $this->response;
    }
}
