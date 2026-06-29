<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum Duration: string
{
    use HasOptions;

    case Singkat = 'singkat';
    case Sedang = 'sedang';
    case Lama = 'lama';

    public function label(): string
    {
        return match ($this) {
            self::Singkat => 'Singkat (<1 jam)',
            self::Sedang => 'Sedang (1-3 jam)',
            self::Lama => 'Lama (>3 jam)',
        };
    }
}
