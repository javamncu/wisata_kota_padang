<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum PriceRange: string
{
    use HasOptions;

    case Gratis = 'gratis';
    case Murah = 'murah';
    case Sedang = 'sedang';
    case Premium = 'premium';

    public function label(): string
    {
        return match ($this) {
            self::Gratis => 'Gratis',
            self::Murah => 'Murah',
            self::Sedang => 'Sedang',
            self::Premium => 'Premium',
        };
    }
}
