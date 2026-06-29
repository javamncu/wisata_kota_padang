<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum TagType: string
{
    use HasOptions;

    case Suasana = 'suasana';
    case Aktivitas = 'aktivitas';
    case Fasilitas = 'fasilitas';

    public function label(): string
    {
        return match ($this) {
            self::Suasana => 'Suasana',
            self::Aktivitas => 'Aktivitas',
            self::Fasilitas => 'Fasilitas',
        };
    }
}
