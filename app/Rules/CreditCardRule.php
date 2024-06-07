<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CreditCardRule implements Rule
{
    /**
     * Chama os metodos para validar o cartao.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->validateCreditCard($value);
    }

    /**
     * Mensagem de erro se cartao invalido.
     *
     * @return string
     */
    public function message()
    {
        return 'O número de cartão de crédito inválido.';
    }

    /**
     * Valida o cartao.
     *
     * @param  string  $number
     * @return bool
     */
    protected function validateCreditCard($number): bool
    {
        $number = preg_replace('/\D/', '', $number);

        if (strlen($number) !== 16) {
            return false;
        }

        return true;
    }
}

