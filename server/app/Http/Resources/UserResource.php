<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      "id" => $this->id,
      'username' => $this->username,
      'email' => $this->email,
      'avatar' => $this->avatar,
      'isVerified' => $this->is_verified,
      'roleId' => $this->role_id,
      'role' => new RoleResource($this->whenLoaded('role')),
      'createdAt' => $this->created_at,
      'updatedAt' => $this->updated_at,
      'token' => $this->when(isset($this->token), $this->token)
    ];
  }
}
