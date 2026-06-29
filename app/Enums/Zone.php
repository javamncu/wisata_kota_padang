<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum Zone: string
{
    use HasOptions;

    case PusatKota = 'pusat_kota';
    case Pesisir = 'pesisir';
    case Selatan = 'selatan';
    case Kepulauan = 'kepulauan';
    case Perbukitan = 'perbukitan';

    public function label(): string
    {
        return match ($this) {
            self::PusatKota => 'Pusat Kota',
            self::Pesisir => 'Pesisir/Pantai',
            self::Selatan => 'Kawasan Selatan',
            self::Kepulauan => 'Kepulauan',
            self::Perbukitan => 'Perbukitan',
        };
    }
}
