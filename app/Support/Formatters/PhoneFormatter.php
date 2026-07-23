<?php

namespace App\Support\Formatters;

class PhoneFormatter
{
    public static function format(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $phone = preg_replace('/\D/', '', $phone);

        if (strlen($phone) === 11) {
            return sprintf(
                '(%s) %s-%s',
                substr($phone, 0, 2),
                substr($phone, 2, 5),
                substr($phone, 7)
            );
        }

        if (strlen($phone) === 10) {
            return sprintf(
                '(%s) %s-%s',
                substr($phone, 0, 2),
                substr($phone, 2, 4),
                substr($phone, 6)
            );
        }

        return null;
    }

    public static function onlyNumbers(?string $phone): ?string
    {
        return $phone ? preg_replace('/\D/', '', $phone) : null;
    }
}
