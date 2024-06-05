<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    public function generatePayment(array $data);
}
