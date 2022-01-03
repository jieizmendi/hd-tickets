<?php

namespace Tests\Feature\API\v1\Ticket\Tag;

use App\Models\Tag;
use App\Models\Ticket;
use App\Models\User;
use Tests\APITestCase;

class TagUserTest extends APITestCase
{
    public function rolesAuthorizationData()
    {
        return [
            'is allowed for admins' => ['Admin', 200],
            'is forbiden for agents' => ['Agent', 403],
            'is forbiden for users' => ['User', 403],
        ];
    }

    /**
     * @dataProvider rolesAuthorizationData
     */
    public function test_tag_an_user(string $role, int $status)
    {
        $this->actingWithRole($role);
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $this->post(route('api.v1.users.tags.add', [
            'user' => $user->id,
            'tag' => $tag->id,
        ]))
            ->assertStatus($status);

        if ($status == 200) {
            $this->assertDatabaseHas('taggables', [
                'taggable_type' => User::class,
                'taggable_id' => $user->id,
                'tag_id' => $tag->id,
            ]);
        }
    }

    public function test_an_user_tags_himself()
    {
        $user = $this->actingWithRole('User');
        $tag = Tag::factory()->create();

        $this->post(route('api.v1.users.tags.add', [
            'user' => $user->id,
            'tag' => $tag->id,
        ]))
            ->assertStatus(200);

        $this->assertDatabaseHas('taggables', [
            'taggable_type' => User::class,
            'taggable_id' => $user->id,
            'tag_id' => $tag->id,
        ]);
    }
}
