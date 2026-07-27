<?php

namespace App\Support\Formatters;

class CnpjFormatter
{
    public static function mask(?string $cnpj): ?string
    {
        if (! $cnpj) {
            return null;
        }

        $cnpj = self::onlyNumbers($cnpj);

        if (strlen($cnpj) !== 14) {
            return null;
        }

        return '**.***.***/****-'.substr($cnpj, -2);
    }

    public static function format(?string $cnpj): ?string
    {
        if (! $cnpj) {
            return null;
        }

        $cnpj = self::onlyNumbers($cnpj);

        if (strlen($cnpj) !== 14) {
            return null;
        }

        return sprintf(
            '%s.%s.%s/%s-%s',
            substr($cnpj, 0, 2),
            substr($cnpj, 2, 3),
            substr($cnpj, 5, 3),
            substr($cnpj, 8, 4),
            substr($cnpj, 12, 2)
        );
    }

    public static function onlyNumbers(?string $cnpj): ?string
    {
        return $cnpj ? preg_replace('/\D/', '', $cnpj) : null;
    }
}
