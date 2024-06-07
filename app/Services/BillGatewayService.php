<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Carbon\Carbon;
use App\Enums\BillingTypeEnum;
use App\Http\Resources\PaymentResource;
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
        $response = [
            'success' => false,
            'status' => 401,
            'data' => null,
            'message' => '',
            'user' => \Auth()->user(),
        ];

        try {
            $today = Carbon::now();
            $dueDate = $today->addDays(15);
    
            $body['customer'] = $this->getCustomer();
            $body['dueDate'] = $dueDate;

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

            $processBody = json_decode((string) $process->getBody(), true);
            $response['dataToSave'] = $this->prepareResponseToColumnsTable($processBody);

            $processBodyResource = new PaymentResource((object) $processBody);            

            $response['data'] = $processBodyResource;            
            $response['success'] = true;
            $response['status'] = $process->getStatusCode();
            $response['message'] = 'Seu pedido foi processado com sucesso. Clique no botão abaixo para acessar o boleto e concluir o pagamento.';
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                $response['status'] = $e->getResponse()->getStatusCode();
                $response['message'] = "Erro inesperado!";
            } else {
                $response['message'] = "Erro desconhecido!";
            }
        }

        return $response;
    }

}
