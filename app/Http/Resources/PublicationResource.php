<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        $data["category_name"] = $this->whenLoaded("category", function () {
            return $this->category->name;
        });

        $data["tags"] = $this->relationLoaded("tags")
            ? TagResource::collection($this->tags)
            : [];

        return $data;
    }
}
