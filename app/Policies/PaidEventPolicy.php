<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PaidEvent;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PaidEventPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PaidEvent');
    }

    public function view(AuthUser $authUser, PaidEvent $paidEvent): bool
    {
        return $authUser->can('View:PaidEvent');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PaidEvent');
    }

    public function update(AuthUser $authUser, PaidEvent $paidEvent): bool
    {
        return $authUser->can('Update:PaidEvent');
    }

    public function delete(AuthUser $authUser, PaidEvent $paidEvent): bool
    {
        return $authUser->can('Delete:PaidEvent');
    }

    public function restore(AuthUser $authUser, PaidEvent $paidEvent): bool
    {
        return $authUser->can('Restore:PaidEvent');
    }

    public function forceDelete(AuthUser $authUser, PaidEvent $paidEvent): bool
    {
        return $authUser->can('ForceDelete:PaidEvent');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PaidEvent');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PaidEvent');
    }

    public function replicate(AuthUser $authUser, PaidEvent $paidEvent): bool
    {
        return $authUser->can('Replicate:PaidEvent');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PaidEvent');
    }
}
