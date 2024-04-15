<?php

namespace App\Enum;

enum ExpensesStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isApproved(): bool
    {
        return $this === self::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this === self::REJECTED;
    }

    public function getLabelText(): string
    {
        return match ($this) {
            self::PENDING => 'pending',
            self::APPROVED => 'approved',
            self::REJECTED => 'rejected'
        };
    }

    public function getLabelColor(): string
    {
        return match ($this) {
            self::PENDING => 'select-warning',
            self::APPROVED => 'select-secondary',
            self::REJECTED => 'select-error'
        };
    }
}
