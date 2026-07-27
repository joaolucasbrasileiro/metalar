<?php

namespace App\Support\Formatters;

class DocumentFormatter
{
    public static function mask(?string $document): ?string
    {
        if (! $document) {
            return null;
        }

        $document = self::onlyNumbers($document);

        return match (strlen($document)) {
            11 => CpfFormatter::mask($document),
            14 => CnpjFormatter::mask($document),
            default => null,
        };
    }

    public static function format(?string $document): ?string
    {
        if (! $document) {
            return null;
        }

        $document = self::onlyNumbers($document);

        return match (strlen($document)) {
            11 => CpfFormatter::format($document),
            14 => CnpjFormatter::format($document),
            default => null,
        };
    }

    public static function onlyNumbers(?string $document): ?string
    {
        return $document ? preg_replace('/\D/', '', $document) : null;
    }
}
