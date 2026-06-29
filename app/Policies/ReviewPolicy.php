<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

/**
 * Admins bypass every check via Gate::before (including moderation and
 * deleting anyone's review). These rules cover normal users.
 */
class ReviewPolicy
{
    /** Any authenticated user may write a review. */
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Review $review): bool
    {
        return $review->user_id === $user->id;
    }

    public function delete(User $user, Review $review): bool
    {
        return $review->user_id === $user->id;
    }
}
