<?php

namespace Tests\Feature\API\v1\Tag;

use App\Models\Tag;
use Illuminate\Support\Arr;
use Tests\APITestCase;

class ReadTagTest extends APITestCase
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
    public function test_read_a_tag(string $role, int $status)
    {
        $this->actingWithRole($role);
        $tag = Tag::factory()->create();

        $response = $this->get(route('api.v1.tags.show', $tag->id))
            ->assertStatus($status);

        if ($status == 200) {
            $response->assertJson(Arr::only($tag->toArray(), ['id', 'name']));
        }
    }
}
