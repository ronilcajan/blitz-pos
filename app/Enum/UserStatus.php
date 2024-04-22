<?php

namespace App\Enum;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case BLOCKED = 'blocked';

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isInActive(): bool
    {
        return $this === self::INACTIVE;
    }

    public function isBlocked(): bool
    {
        return $this === self::BLOCKED;
    }

    public function getLabelText(): string
    {
        return match ($this) {
            self::ACTIVE => 'active',
            self::INACTIVE => 'inactive',
            self::BLOCKED => 'blocked'
        };
    }

    public function getLabelColor(): string
    {
        return match ($this) {
            self::ACTIVE => 'select-primary',
            self::INACTIVE => 'select-bordered',
            self::BLOCKED => 'select-bordered'
        };
    }

}
