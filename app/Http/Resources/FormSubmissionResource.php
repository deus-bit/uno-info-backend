<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormSubmissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $data["form"] = new FormResource($this->whenLoaded("form"));
        $data["attachment_media"] = new MediaResource(
            $this->whenLoaded("attachmentMedia"),
        );
        return $data;
    }
}
