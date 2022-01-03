<?php

namespace Tests\Feature\API\v1\Tag;

use App\Models\Tag;
use Tests\APITestCase;

class DeleteTagTest extends APITestCase
{
    public function rolesAuthorizationData()
    {
        return [
            'is allowed for admins' => ['Admin', 204],
            'is forbiden for agents' => ['Agent', 403],
            'is forbiden for users' => ['User', 403],
        ];
    }

    /**
     * @dataProvider rolesAuthorizationData
     */
    public function test_delete_a_tag(string $role, int $status)
    {
        $this->actingWithRole($role);
        $tag = Tag::factory()->create();

        $this->delete(route('api.v1.tags.destroy', $tag->id))
            ->assertStatus($status);

        if ($status == 204) {
            $this->assertSoftDeleted('tags', ['id' => $tag->id]);
        }
    }
}
