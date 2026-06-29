<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum Status: string
{
    use HasOptions;

    case Draft = 'draft';
    case Aktif = 'aktif';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Aktif => 'Aktif',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Aktif;
    }
}
