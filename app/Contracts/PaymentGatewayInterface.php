<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    public function process(array $data);
    public function getCustomer(array $data);
    public function send(array $data);
    public function handleResponse(array $data);
    public function handleSend(array $data);    

}
