<?php

namespace App\Support\Formatters;

class CpfFormatter
{
    public static function mask(?string $cpf): ?string
    {
        if (! $cpf) {
            return null;
        }

        $cpf = preg_replace('/\D/', '', $cpf);

        if (strlen($cpf) !== 11) {
            return null;
        }

        return '***.***.***-'.substr($cpf, -2);
    }

    public static function format(?string $cpf): ?string
    {
        if (! $cpf) {
            return null;
        }

        $cpf = preg_replace('/\D/', '', $cpf);

        if (strlen($cpf) !== 11) {
            return null;
        }

        return sprintf(
            '%s.%s.%s-%s',
            substr($cpf, 0, 3),
            substr($cpf, 3, 3),
            substr($cpf, 6, 3),
            substr($cpf, 9, 2)
        );
    }

    public static function onlyNumbers(?string $cpf): ?string
    {
        return $cpf ? preg_replace('/\D/', '', $cpf) : null;
    }
}
