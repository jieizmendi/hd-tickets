<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class StatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,

            'content' => $this->when(
                !(Auth::user()->isUser() && !$this->is_public),
                $this->content
            ),

            'user' => new UserResource($this->user),

            'created_at' => $this->created_at,
        ];
    }
}
