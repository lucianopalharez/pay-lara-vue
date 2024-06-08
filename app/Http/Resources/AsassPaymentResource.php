<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class AsassPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'invoiceNumber' => $this->invoiceNumber,
            'bankSlipUrl' => $this->bankSlipUrl,
            'invoiceUrl' => $this->invoiceUrl,
            'externalReference' => $this->externalReference,
            'description' => $this->description,
            'status' => $this->status,
            'pixTransaction' => $this->pixTransaction,
            'billingType' => $this->billingType,
            'value' => $this->value,
            'dueDate' => $this->dueDate,
            'paymentCreated' => $this->dateCreated,
            'dueDateFormated' => Carbon::createFromFormat('Y-m-d', $this->dueDate)->format('d/m/Y'),
        ];
    }
}
