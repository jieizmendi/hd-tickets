<?php

namespace Tests\Feature\API\v1\Tag;

use App\Models\Tag;
use Illuminate\Support\Arr;
use Tests\APITestCase;

class UpdateTagTest extends APITestCase
{
    public function rolesAuthorizationData()
    {
        return [
            'is allowed for admins' => ['Admin', 200],
            'is forbiden for agents' => ['Agent', 403],
            'is forbiden for users' => ['User', 403],
        ];
    }

    public function updateInvalidData()
    {
        return [
            'missing label' => [
                [],
                ['label'],
            ],

            'not string label' => [
                [
                    'label' => [],
                ],
                ['label'],
            ],

            'label length is less than 3.' => [
                [
                    'label' => 'aa',
                ],
                ['label'],
            ],

            'label length is more than 50.' => [
                [
                    'label' => str_repeat('a', 100),
                ],
                ['label'],
            ],

            'label is not unique' => [
                [
                    'label' => 'not-unique',
                ],
                ['label'],
            ],
        ];
    }

    /**
     * @dataProvider rolesAuthorizationData
     */
    public function test_update_a_tag(string $role, int $status)
    {
        $this->actingWithRole($role);
        $tag = Tag::factory()->create();
        $raw = Arr::only(Tag::factory()->make()->toArray(), ['label']);
        $assertData = Arr::only(array_merge($tag->toArray(), $raw), ['id', 'name']);

        $response = $this->put(route('api.v1.tags.update', $tag->id), $raw)
            ->assertStatus($status);

        if ($status === 200) {

            $response->assertJson($assertData);
            $this->assertDatabaseHas('tags', $assertData);
        }
    }

    public function test_an_admin_update_a_tag_with_a_not_unique_name_used_in_a_soft_deleted_tag()
    {
        $this->actingWithRole('Admin');
        $tagDeleted = Tag::factory()->create();
        $tagDeleted->delete();
        $raw = ['label' => $tagDeleted->label];
        $tag = Tag::factory()->create();
        $assertData = Arr::only(array_merge($tag->toArray(), $raw), ['id', 'name']);

        $this->put(route('api.v1.tags.update', $tag->id), $raw)
            ->assertStatus(200)
            ->assertJson($assertData);

        $this->assertDatabaseHas('tags', $assertData);
    }

    public function test_an_admin_update_a_tag_with_the_same_name()
    {
        $this->actingWithRole('Admin');
        $tag = Tag::factory()->create();
        $assertData = Arr::only($tag->toArray(), ['id', 'label']);

        $this->put(route('api.v1.tags.update', $tag->id), $tag->toArray())
            ->assertStatus(200)
            ->assertJson($assertData);

        $this->assertDatabaseHas('tags', $assertData);
    }

    /**
     * @dataProvider updateInvalidData
     */
    public function test_an_admin_fails_update_a_tag_validation(array $data, array $fields)
    {
        $this->actingWithRole('Admin');
        Tag::factory()->create(['label' => 'not-unique']);
        $tag = Tag::factory()->create();

        $this->put(route('api.v1.tags.update', $tag->id), $data)
            ->assertStatus(422)
            ->assertInvalid($fields);
    }
}
