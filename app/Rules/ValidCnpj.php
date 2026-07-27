<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCnpj implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cnpj = preg_replace('/\D/', '', (string) $value);

        if (strlen($cnpj) !== 14) {
            $fail('O CNPJ deve conter exatamente 14 dígitos.');

            return;
        }

        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            $fail('O CNPJ informado é inválido.');

            return;
        }

        if (
            $this->calculateDigit($cnpj, 12) !== (int) $cnpj[12]
            || $this->calculateDigit($cnpj, 13) !== (int) $cnpj[13]
        ) {
            $fail('O CNPJ informado é inválido.');
        }
    }

    private function calculateDigit(string $cnpj, int $length): int
    {
        $weights = $length === 12
            ? [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
            : [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $sum = 0;

        for ($index = 0; $index < $length; $index++) {
            $sum += (int) $cnpj[$index] * $weights[$index];
        }

        $remainder = $sum % 11;

        return $remainder < 2 ? 0 : 11 - $remainder;
    }
}
