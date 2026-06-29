<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum ReviewStatus: string
{
    use HasOptions;

    case Pending = 'pending';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu moderasi',
            self::Published => 'Dipublikasikan',
        };
    }

    public function isPublished(): bool
    {
        return $this === self::Published;
    }
}
