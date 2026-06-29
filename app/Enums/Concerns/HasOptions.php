<?php

namespace App\Enums\Concerns;

/**
 * Helpers shared by every string-backed enum in the app.
 *
 * Each enum still defines its own label() for the human-readable text;
 * this trait builds the form/select helpers on top of that.
 */
trait HasOptions
{
    /** Human-readable label for the case. */
    abstract public function label(): string;

    /** All backing values, e.g. ['gratis', 'murah', ...]. */
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }

    /** value => label map, handy for <select> options. */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /** [['value' => ..., 'label' => ...], ...], handy for loops/JSON. */
    public static function toArray(): array
    {
        return array_map(
            fn (self $case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases(),
        );
    }

    /** Safe lookup that returns null instead of throwing. */
    public static function fromValue(?string $value): ?self
    {
        return $value === null ? null : self::tryFrom($value);
    }
}
