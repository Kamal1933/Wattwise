<?php

namespace App\Policies;

use App\Models\Household;
use App\Models\User;

class HouseholdPolicy
{
    /**
     * Determine whether the user can view the household dashboard.
     */
    public function view(User $user, Household $household): bool
    {
        return $this->ownsOrAdministers($user, $household);
    }

    /**
     * Determine whether the user can modify the household, its appliances, and its meter readings.
     */
    public function update(User $user, Household $household): bool
    {
        return $this->ownsOrAdministers($user, $household);
    }

    /**
     * Determine whether the user is the household owner or an administrator.
     */
    protected function ownsOrAdministers(User $user, Household $household): bool
    {
        return $user->isAdmin() || (int) $household->user_id === (int) $user->id;
    }
}
