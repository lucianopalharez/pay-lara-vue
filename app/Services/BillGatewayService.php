<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Carbon\Carbon;
use App\Enums\BillingTypeEnum;
use App\Http\Resources\BillPaymentResource;
use App\Services\GatewayService;
use GuzzleHttp\Psr7\Uri;
use App\Models\User;

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
            'user' => null
        ];

        try {
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

            $processBodyResource = new BillPaymentResource((object) $processBody);            

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
