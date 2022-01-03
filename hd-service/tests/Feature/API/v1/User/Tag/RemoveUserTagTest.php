<?php

namespace Tests\Feature\API\v1\Ticket\Tag;

use App\Models\Tag;
use App\Models\Ticket;
use App\Models\User;
use Tests\APITestCase;

class RemoveUserTagTest extends APITestCase
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
    public function test_remove_a_tag_from_an_user(string $role, int $status)
    {
        $this->actingWithRole($role);
        $user = User::factory()->create();
        $tag = Tag::factory()->create();
        $user->tag($tag);

        $this->delete(route('api.v1.users.tags.remove', [
            'user' => $user->id,
            'tag' => $tag->id,
        ]))
            ->assertStatus($status);

        if ($status == 200) {
            $this->assertDatabaseMissing('taggables', [
                'taggable_type' => User::class,
                'taggable_id' => $user->id,
                'tag_id' => $tag->id,
            ]);
        } else {
            $this->assertDatabaseHas('taggables', [
                'taggable_type' => User::class,
                'taggable_id' => $user->id,
                'tag_id' => $tag->id,
            ]);
        }
    }

    public function test_an_agent_removes_his_own_tag()
    {
        $agent = $this->actingWithRole('Agent');
        $tag = Tag::factory()->create();
        $agent->tag($tag);

        $this->delete(route('api.v1.users.tags.remove', [
            'user' => $agent->id,
            'tag' => $tag->id,
        ]))
            ->assertStatus(200);

        $this->assertDatabaseMissing('taggables', [
            'taggable_type' => User::class,
            'taggable_id' => $agent->id,
            'tag_id' => $tag->id,
        ]);
    }
}
