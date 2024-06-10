<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use App\Enums\BillingTypeEnum;

class AsassPaymentResource extends JsonResource
{
    /**
     * Transforma os dados recebidos da ASASS para os suportados na aplicação.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $billingType = '';

        if (empty($this->billingType) === false) {
            $billingType = $this->billingType === 'UNDEFINED' ? BillingTypeEnum::CREDIT_CARD->name : $this->billingType;
        }
        
        return [
            'billingType' => $billingType,
            'billingId' => empty($this->id) === false ? $this->id : '',
            'invoiceNumber' => empty($this->invoiceNumber) === false ?  $this->invoiceNumber : '',
            'bankSlipUrl' => empty($this->bankSlipUrl) === false ? $this->bankSlipUrl : '',
            'invoiceUrl' => empty($this->invoiceUrl) === false ? $this->invoiceUrl : '',
            'externalReference' => empty($this->externalReference) === false ? $this->externalReference : '',
            'description' => empty($this->description) === false ? $this->description : '',
            'status' => empty($this->status) === false ? $this->status : '',
            'pixTransaction' => empty($this->pixTransaction) === false ? $this->pixTransaction : '',            
            'value' => empty($this->value) === false ? $this->value : '',
            'dueDate' => empty($this->dueDate) === false ? $this->dueDate : '',
            'paymentCreated' => empty($this->dateCreated) === false ? $this->dateCreated : '',
            'dueDateFormated' => empty($this->dueDate) === false ? Carbon::createFromFormat('Y-m-d', $this->dueDate)->format('d/m/Y') : '',
            'encodedImage' => empty($this->encodedImage) === false ? $this->encodedImage : '',
            'payload' => empty($this->payload) === false ? $this->payload : '',
            'expirationDate' => empty($this->expirationDate) === false ? $this->expirationDate : '',
        ];
    }
}


