<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Response;

class ResponsePolicy
{
  public function showResponse(User $user, Response $response): bool
  {
    return $user->role->name === 'admin' || $user->id === $response->user_id;
  }
}
