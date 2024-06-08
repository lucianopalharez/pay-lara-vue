<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CpfCnpjRule;
use App\Rules\CreditCardRule;

class PaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => 'required',
            'description' => 'required',
            'value' => 'required',
            'billingType' => 'required',
            'invoiceNumber' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'value.required' => 'O valor do pagamento é inválido',
            'description.required' => 'A descrição do pagamento é inválida',
            'status.required' => 'Status de pagamento inválido',
            'billingType.required' => 'O meio de pagamento é inválido',
            'invoiceNumber.required' => 'O numero do pedido é inválido',
        ]; 
    }
}
