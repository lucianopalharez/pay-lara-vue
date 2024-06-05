<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BillPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:1',
            'payer_name' => 'required|string|max:255',
            'payer_email' => 'required|email|max:255',
            // Adicione outras regras conforme necessário
        ];
    }
}
