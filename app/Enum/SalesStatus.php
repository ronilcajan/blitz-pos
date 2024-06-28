<?php

namespace App\Enum;

enum SalesStatus: string
{
    case PENDING = 'pending';
    case COMPLETE = 'complete';
    case VOID = 'void';

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isComplete(): bool
    {
        return $this === self::COMPLETE;
    }

    public function isVoid(): bool
    {
        return $this === self::VOID;
    }

    public function getLabelText(): string
    {
        return match ($this) {
            self::PENDING => 'pending',
            self::COMPLETE => 'complete',
            self::VOID => 'void'
        };
    }

    public function getLabelColor(): string
    {
        return match ($this) {
            self::PENDING => 'yellow-500',
            self::COMPLETE => 'success',
            self::VOID => 'red-500 '
        };
    }
}
