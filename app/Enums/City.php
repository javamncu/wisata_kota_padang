<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * Cities (kota) in West Sumatra covered by the directory. A locked set in
 * code — like Zone — not an admin-managed table. Padang is the default for
 * every existing destination.
 */
enum City: string
{
    use HasOptions;

    case Padang = 'padang';
    case Bukittinggi = 'bukittinggi';
    case PadangPanjang = 'padang_panjang';
    case Pariaman = 'pariaman';
    case Payakumbuh = 'payakumbuh';
    case Sawahlunto = 'sawahlunto';
    case Solok = 'solok';

    public function label(): string
    {
        return match ($this) {
            self::Padang => 'Padang',
            self::Bukittinggi => 'Bukittinggi',
            self::PadangPanjang => 'Padang Panjang',
            self::Pariaman => 'Pariaman',
            self::Payakumbuh => 'Payakumbuh',
            self::Sawahlunto => 'Sawahlunto',
            self::Solok => 'Solok',
        };
    }
}
