<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum ArticleStatus: string
{
    use HasOptions;

    case Draft = 'draft';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Dipublikasikan',
        };
    }

    public function isPublished(): bool
    {
        return $this === self::Published;
    }
}
