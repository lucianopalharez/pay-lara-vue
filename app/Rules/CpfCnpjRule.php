<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CpfCnpjRule implements Rule
{
    public function passes($attribute, $value)
    {
        return (strlen($value) === 11 || strlen($value) === 14);
    }

    public function message()
    {
        return 'Digite um CPF ou CNPJ válido';
    }
}