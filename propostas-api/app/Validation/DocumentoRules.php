<?php

namespace App\Validation;

class DocumentoRules
{
    /**
     * Validate CPF or CNPJ
     */
    public function valid_documento(string $str, ?string &$error = null): bool
    {
        // Remove non-numeric characters
        $documento = preg_replace('/[^0-9]/', '', $str);

        // Check if it's CPF (11 digits) or CNPJ (14 digits)
        if (strlen($documento) === 11) {
            return $this->validarCPF($documento, $error);
        } elseif (strlen($documento) === 14) {
            return $this->validarCNPJ($documento, $error);
        }

        $error = 'CPF/CNPJ deve ter 11 ou 14 dígitos';
        return false;
    }

    /**
     * Validate CPF
     */
    protected function validarCPF(string $cpf, ?string &$error = null): bool
    {
        // Check if all digits are the same
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            $error = 'CPF inválido';
            return false;
        }

        // Validate first check digit
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int)$cpf[$i] * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = ($remainder < 2) ? 0 : 11 - $remainder;

        if ((int)$cpf[9] !== $digit1) {
            $error = 'CPF inválido';
            return false;
        }

        // Validate second check digit
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int)$cpf[$i] * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = ($remainder < 2) ? 0 : 11 - $remainder;

        if ((int)$cpf[10] !== $digit2) {
            $error = 'CPF inválido';
            return false;
        }

        return true;
    }

    /**
     * Validate CNPJ
     */
    protected function validarCNPJ(string $cnpj, ?string &$error = null): bool
    {
        // Check if all digits are the same
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            $error = 'CNPJ inválido';
            return false;
        }

        // Validate first check digit
        $sum = 0;
        $weights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$cnpj[$i] * $weights[$i];
        }
        $remainder = $sum % 11;
        $digit1 = ($remainder < 2) ? 0 : 11 - $remainder;

        if ((int)$cnpj[12] !== $digit1) {
            $error = 'CNPJ inválido';
            return false;
        }

        // Validate second check digit
        $sum = 0;
        $weights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 13; $i++) {
            $sum += (int)$cnpj[$i] * $weights[$i];
        }
        $remainder = $sum % 11;
        $digit2 = ($remainder < 2) ? 0 : 11 - $remainder;

        if ((int)$cnpj[13] !== $digit2) {
            $error = 'CNPJ inválido';
            return false;
        }

        return true;
    }
}
