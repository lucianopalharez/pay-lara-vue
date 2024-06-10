<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    public function createPayment(array $data);
    public function finallyPayment(array $data);
    public function getCustomer(array $data);
    public function send(array $data);
    public function handleSend(array $data);    

}
