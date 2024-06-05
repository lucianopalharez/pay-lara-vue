<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => $this->status,
            'boleto_url' => $this->boleto_url,
            'amount' => $this->amount,
            'payer_name' => $this->payer_name,
            'due_date' => $this->due_date,
            // Adicione outros campos conforme necessário
        ];
    }
}
