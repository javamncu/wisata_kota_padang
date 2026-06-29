<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum IndoorOutdoor: string
{
    use HasOptions;

    case Indoor = 'indoor';
    case Outdoor = 'outdoor';
    case Campuran = 'campuran';

    public function label(): string
    {
        return match ($this) {
            self::Indoor => 'Indoor',
            self::Outdoor => 'Outdoor',
            self::Campuran => 'Campuran',
        };
    }
}
