<?php

namespace App\Http\Controllers\API\v1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\UserResource;
use App\Models\Tag;
use App\Models\User;

class TagController extends Controller
{
    public function add(User $user, Tag $tag)
    {
        $this->authorize('tags', $user);

        $user->tag($tag);

        return new UserResource($user);
    }

    public function remove(User $user, Tag $tag)
    {
        $this->authorize('tags', $user);

        $user->untag($tag);

        return new UserResource($user);
    }
}
