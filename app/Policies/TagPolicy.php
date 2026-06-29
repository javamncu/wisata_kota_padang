<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;

/**
 * Admins bypass every check via Gate::before.
 */
class TagPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Tag $tag): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Tag $tag): bool
    {
        return false;
    }

    public function delete(User $user, Tag $tag): bool
    {
        return false;
    }
}
