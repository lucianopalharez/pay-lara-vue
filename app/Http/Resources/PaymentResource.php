<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

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
            'invoiceUrl' => $this->invoiceUrl,
            'value' => $this->value,
            'pixTransaction' => $this->pixTransaction,
            'dueDate' => Carbon::createFromFormat('Y-m-d', $this->dueDate)->format('d/m/Y'),
            'billingType' => $this->billingType,
            'invoiceNumber' => $this->invoiceNumber
        ];
    }
}
