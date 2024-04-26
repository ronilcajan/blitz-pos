<?php

namespace App\Classes;

class TransactionCodeGenerator
{
    private const DATE_FORMAT = 'Ymds';

    public function generate(): string
    {
        $timestamp = $this->getCurrentTimestamp();
        $randomNumber = $this->generateRandomAlphanumericCode(4);

        return $timestamp . auth()->id(). $randomNumber;
    }

    private function getCurrentTimestamp(): int
    {
        return date(self::DATE_FORMAT);
    }

    private function generateRandomAlphanumericCode($length) {
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }
}
