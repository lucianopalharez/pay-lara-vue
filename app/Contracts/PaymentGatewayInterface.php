<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Faz requisição no gateway de pagamento para criar uma cobrança.
     *
     * @param  array  $body
     * @return array
     */
    public function createPayment(array $body);

    /**
     * Faz requisição no gateway de pagamento para finalizar uma cobrança.
     *
     * @param  array  $body
     * @return array
     */
    public function finallyPayment(array $body);

    /**
     * Pega codigo do cliente do gateway de pagamento.
     *
     * @param  array $body
     * @return string
     */
    public function getCustomer(array $body);


    /**
     * Envia requisição para o gateway de pagamento.
     *
     * @param  array  $body
     * @return array
     */
    public function send(array $body);

    /**
     * Trata os dados de pagamento antes de enviar.
     *
     * @param  array $body    Dados para envio.
     * @return array
     */
    public function handleSendPayment(array $body);

    /**
     * Trata os dados recebidos.
     *
     * @param  array $body      Dados para envio.
     * @param  array $response  Dados recebidos.
     * @return array
     */
    public function handleResponse(array $body, array $response);    

}
