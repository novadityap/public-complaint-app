<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResponseResource extends JsonResource
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
      "message" => $this->message,
      'complaintId' => $this->complaint_id,
      'userId' => $this->user_id,
      'user' => new UserResource($this->whenLoaded('user')),
      'createdAt' => $this->created_at,
      'updatedAt' => $this->updated_at
    ];
  }
}
