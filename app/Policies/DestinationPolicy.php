<?php

namespace App\Policies;

use App\Enums\Status;
use App\Models\Destination;
use App\Models\User;

/**
 * Admins bypass every check via Gate::before (see AppServiceProvider),
 * so these methods only describe what guests and normal users may do.
 */
class DestinationPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Destination $destination): bool
    {
        return $destination->status === Status::Aktif;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Destination $destination): bool
    {
        return false;
    }

    public function delete(User $user, Destination $destination): bool
    {
        return false;
    }

    public function restore(User $user, Destination $destination): bool
    {
        return false;
    }

    public function forceDelete(User $user, Destination $destination): bool
    {
        return false;
    }
}
