<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CpfCnpjRule;

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
        $rules = [
            'billingType' => 'required|in:CREDIT_CARD,BOLETO,PIX',
            'description' => 'required|string|max:255',
            'value' => 'required|numeric|min:1',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'cpfCnpj' => ['required','numeric',new CpfCnpjRule],
            'postalCode' => 'nullable|numeric|digits:8',
        ];

        if ($this->request->get('billingType') === 'CREDIT_CARD') {
            $rules = array_merge($rules, [
                'creditCardNumber' => 'required|credit_card',
                'expiryMonth' => 'required|digits:2',
                'expiryYear' => 'required|digits:4',
                'cvv' => 'required|digits:3',                
            ]);
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'creditCardNumber.required' => 'Digite o numero do cartão',
            'creditCardNumber.credit_card' => 'Numero do cartão inválido',
            'name.required' => 'Digite o nome',
            'name.max' => 'O nome não pode exceder 255 caracteres',
            'expiryMonth.required' => 'Digite o mẽs de expiração do cartão',
            'expiryMonth.digits' => 'O mês de expiração do cartão deve conter 2 digitos',
            'expiryYear.required' => 'Digite o ano do cartão',
            'expiryYear.digits' => 'O ano do cartão deve conter 4 digitos',
            'cvv.required' => 'Digite o numero CVV',
            'cvv.digits' => 'O numero CVV deve conter 3 digitos',
            'value.required' => 'Digite um valor',
            'value.numeric' => 'O valor deve ser um numero',
            'value.min' => 'O valor deve conter pelo menos 1 digito',
            'billingType.required' => 'Selecione o meio de pagamento',
            'billingType.in' => 'O meio de pagamento deve ser CREDIT_CARD, BOLETO ou PIX',
            'description.required' => 'Digite a descrição do pagamento',
            'description.max' => 'A descrição do pagamento deve conter no maximo 255 caracteres',
            'email.required' => 'Digite o email',
            'email.max' => 'O email deve conter no maximo 255 caracteres',
            'email.email' => 'Digite um email válido',
            'cpfCnpj.required' => 'Digite um cpf ou cnpj',
            'cpfCnpj.numeric' => 'O cpf ou cnpj deve conter apenas numeros',
            'postalCode.numeric' => 'O cep deve conter apenas numeros',
            'postalCode.digits' => 'Digite um cep válido',
        ];
    }
}
