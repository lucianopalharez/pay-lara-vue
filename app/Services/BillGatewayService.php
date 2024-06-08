<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Carbon\Carbon;
use App\Enums\BillingTypeEnum;
use App\Http\Resources\BillPaymentResource;
use App\Services\GatewayService;

class BillGatewayService extends GatewayService implements PaymentGatewayInterface
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
            $processBody = $this->send($body);            

            $processBodyResource = new BillPaymentResource((object) $processBody);            

            $this->response['data'] = $processBodyResource;       
            $this->response['message'] = 'Seu pedido foi processado com sucesso. Clique no botão abaixo para acessar o boleto e concluir o pagamento.';

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

}
