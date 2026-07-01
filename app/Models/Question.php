<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A public Q&A ("Tanya Jawab") entry: a visitor's question and the admin's
 * answer. Questions appear publicly as soon as they are posted; the admin can
 * answer them or hide spam/abuse.
 */
class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'author_name',
        'question',
        'answer',
        'answered_at',
        'is_hidden',
    ];

    protected function casts(): array
    {
        return [
            'answered_at' => 'datetime',
            'is_hidden' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Has the admin answered this question yet? */
    public function isAnswered(): bool
    {
        return $this->answer !== null && trim($this->answer) !== '';
    }

    // -- Scopes ---------------------------------------------------------

    /** Questions the public is allowed to see (not hidden by admin). */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_hidden', false);
    }

    public function scopeAnswered(Builder $query): Builder
    {
        return $query->whereNotNull('answer');
    }

    public function scopeUnanswered(Builder $query): Builder
    {
        return $query->whereNull('answer');
    }
}
