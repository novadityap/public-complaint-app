<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Complaint;

class ComplaintPolicy
{
  public function show(User $user, Complaint $complaint): bool
    {
      return $user->role->name === 'admin' || $user->id === $complaint->user_id;
    }
    
    public function update(User $user, Complaint $complaint): bool
    {
       return $user->role->name === 'admin' || $user->id === $complaint->user_id;
    }

    public function delete(User $user, Complaint $complaint): bool
    {
       return $user->role->name === 'admin' || $user->id === $complaint->user_id;
    }

     public function deleteImage(User $user, Complaint $complaint): bool
    {
       return $user->role->name === 'admin' || $user->id === $complaint->user_id;
    }

     public function listResponses(User $user, Complaint $complaint): bool
    {
      return $user->role->name === 'admin' || $user->id === $complaint->user_id;
    }
}
