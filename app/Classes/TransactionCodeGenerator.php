<?php

namespace App\Classes;

use App\Models\Purchase;

class TransactionCodeGenerator
{
    private const DATE_FORMAT = 'ymd';

    public function generate(): string
    {
        $timestamp = $this->getCurrentTimestamp();
        $orderCount = $this->countPurchaseOrder();

        return $timestamp . auth()->id() . "00" . $orderCount;
    }

    private function getCurrentTimestamp(): int
    {
        return date(self::DATE_FORMAT);
    }

    private function countPurchaseOrder() {

        $count = Purchase::count();
        return $count ? 1 + $count : 1;
    }
}
