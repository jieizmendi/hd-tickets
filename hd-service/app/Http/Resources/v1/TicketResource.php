<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class TicketResource extends JsonResource
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
            'subject' => $this->subject,
            'content' => $this->content,
            'status' => $this->status->name,

            'agents' => UserResource::collection($this->agents),
            'owner' => new UserResource($this->owner),
            'priority' => (int) $this->priority,

            'statuses' => StatusResource::collection($this->statuses),

            'files' => $this->visibleFiles(),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Resolve files visibility.
     */
    public function visibleFiles(): AnonymousResourceCollection
    {
        if (Auth::user()->isUser()) {
            return FileResource::collection($this->files()->onlyPublic()->get());
        }

        return FileResource::collection($this->files);
    }
}
