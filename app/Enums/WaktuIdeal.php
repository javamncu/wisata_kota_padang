<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * Multi-value attribute. Stored on destinations as a JSON array of these
 * backing values, queried with whereJsonContains().
 */
enum WaktuIdeal: string
{
    use HasOptions;

    case Pagi = 'pagi';
    case Siang = 'siang';
    case Sore = 'sore';
    case Malam = 'malam';

    public function label(): string
    {
        return match ($this) {
            self::Pagi => 'Pagi',
            self::Siang => 'Siang',
            self::Sore => 'Sore/sunset',
            self::Malam => 'Malam',
        };
    }
}
