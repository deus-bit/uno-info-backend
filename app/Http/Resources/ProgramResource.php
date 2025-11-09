<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'faculty_id' => $this->faculty_id,
            'faculty_name' => $this->whenLoaded('faculty', function () {
                return $this->faculty->name;
            }),
            'name' => $this->name,
            'slug' => $this->slug,
            'degree_type' => $this->degree_type,
            'duration_semesters' => $this->duration_semesters,
            'modality' => $this->modality,
            'description' => $this->description,
            'people' => PersonResource::collection($this->whenLoaded('people')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
