<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $data["photo_media"] = new MediaResource(
            $this->whenLoaded("photoMedia"),
        );
        $data["programs"] = ProgramResource::collection(
            $this->whenLoaded("programs"),
        );
        return $data;
    }
}
