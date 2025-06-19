<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class UserPolicy
{
    public function profile(User $authUser, User $user): bool
    {
      return $authUser->id === $user->id || $authUser->role->name === 'admin';
    }

    public function update(User $authUser, User $user): bool {
      return $authUser->id === $user->id || $authUser->role->name === 'admin';
    }
}
