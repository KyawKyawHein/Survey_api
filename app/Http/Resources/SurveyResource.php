<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class SurveyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=>$this->id,
            "title"=>$this->title,
            "slug"=>$this->slug,
            "image"=>$this->image? URL::to($this->image):null,
            "status"=>$this->status,
            "description"=>$this->description,
            'expire_date' => (new \DateTime($this->expire_date))->format('Y-m-d'),
        ];
    }
}
