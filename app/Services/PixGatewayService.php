<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use App\Enums\BillingTypeEnum;
use App\Http\Resources\PixPaymentResource;
use App\Services\GatewayService;

class PixGatewayService extends GatewayService implements PaymentGatewayInterface
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
            $body['format'] = 'ALL';
            $body['expirationSeconds'] = '600';
            $body['externalReference'] = $body['ip'];

            $processBody = $this->send($body);

            $processBodyResource = new PixPaymentResource((object) $processBody);            

            $this->response['data'] = $processBodyResource;          
            $this->response['message'] = 'Seu pedido foi processado com sucesso. Faça o pagamento pelo QR code.';

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
