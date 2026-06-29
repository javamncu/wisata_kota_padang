<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * Multi-value attribute. Stored on destinations as a JSON array of these
 * backing values, queried with whereJsonContains().
 */
enum CocokUntuk: string
{
    use HasOptions;

    case Keluarga = 'keluarga';
    case Pasangan = 'pasangan';
    case Solo = 'solo';
    case Rombongan = 'rombongan';
    case Lansia = 'lansia';
    case RamahDifabel = 'ramah_difabel';

    public function label(): string
    {
        return match ($this) {
            self::Keluarga => 'Keluarga & anak',
            self::Pasangan => 'Pasangan',
            self::Solo => 'Solo',
            self::Rombongan => 'Rombongan',
            self::Lansia => 'Lansia',
            self::RamahDifabel => 'Ramah difabel',
        };
    }
}
