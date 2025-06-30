<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
          "id"=> $this->id,
          "title" => $this->title,
          "description" => $this->description,
          "status" => $this->status,
          'images' => $this->images,
          'categoryId' => $this->category_id,
          'userId' => $this->user_id,
          'user' => new UserResource($this->whenLoaded('user')),
          'category' => new CategoryResource($this->whenLoaded('category')),
          "createdAt" => $this->created_at,
          "updatedAt" => $this->updated_at
        ];
    }
}
