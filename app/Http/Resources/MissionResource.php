<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'quest' => $this->whenLoaded('quest', function () {
                return [
                    'id' => $this->quest->id,
                    'title' => $this->quest->title,
                ];
            }),
            'heroes' => $this->whenLoaded('heroes', function () {
                return $this->heroes->map(fn ($hero) => [
                    'id' => $hero->id,
                    'name' => $hero->name,
                ]);
            }),
            'completed_at' => $this->completed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

