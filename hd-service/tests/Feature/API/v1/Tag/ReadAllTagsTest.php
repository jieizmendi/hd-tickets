<?php

namespace Tests\Feature\API\v1\Tag;

use App\Models\Tag;
use Tests\APITestCase;

class ReadAllTagsTest extends APITestCase
{
    public function rolesAuthorizationData()
    {
        return [
            'is allowed for admins' => ['Admin', 200],
            'is allowed for agents' => ['Agent', 200],
            'is allowed for users' => ['User', 200],
        ];
    }

    /**
     * @dataProvider rolesAuthorizationData
     */
    public function test_read_all_tags(string $role, int $status)
    {
        $this->actingWithRole($role);
        Tag::factory()->times(17)->create();

        $response = $this->get(route('api.v1.tags.index'))
            ->assertStatus($status);

        if ($status == 200) {
            $response->assertJsonCount(17);
        }
    }
}
