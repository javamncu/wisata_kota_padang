<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

/**
 * Admins bypass every check via Gate::before. The "cannot delete a
 * category that still has destinations" rule is a business constraint
 * enforced in the controller (Category::isDeletable), not here — so it
 * is not silently bypassed for admins.
 */
class CategoryPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Category $category): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Category $category): bool
    {
        return false;
    }

    public function delete(User $user, Category $category): bool
    {
        return false;
    }
}
