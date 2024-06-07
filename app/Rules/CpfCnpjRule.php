<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CpfCnpjRule implements Rule
{

    /**
     * Valida se o cpf ou cnpj é valido.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->validateCpfCnpj($value);
    }

    /**
     * Mensagem de validação.
     *
     * @return string
     */
    public function message()
    {
        return 'Digite um CPF ou CNPJ válido';
    }

    /**
     * Validate CPF or CNPJ.
     *
     * @param  string  $value
     * @return bool
     */
    protected function validateCpfCnpj($value)
    {
        $value = preg_replace('/[^0-9]/', '', $value);

        if (strlen($value) === 11) {
            return $this->validateCpf($value);
        } elseif (strlen($value) === 14) {
            return $this->validateCnpj($value);
        }

        return false;
    }

    /**
     * Validate CPF.
     *
     * @param  string  $cpf
     * @return bool
     */
    protected function validateCpf($cpf)
    {
        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate CNPJ.
     *
     * @param  string  $cnpj
     * @return bool
     */
    protected function validateCnpj($cnpj)
    {
        if (strlen($cnpj) != 14 || preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        for ($t = 12; $t < 14; $t++) {
            for ($d = 0, $c = 0, $p = $t - 7; $c < $t; $c++) {
                $d += $cnpj[$c] * $p;
                $p = ($p == 2) ? 9 : --$p;
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cnpj[$c] != $d) {
                return false;
            }
        }

        return true;
    }
}