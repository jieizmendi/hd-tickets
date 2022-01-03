<?php

namespace Tests\Feature\API\v1\Tag;

use App\Models\Tag;
use Tests\APITestCase;

class StoreTagTest extends APITestCase
{
    public function rolesAuthorizationData()
    {
        return [
            'is allowed for admins' => ['Admin', 201],
            'is forbiden for agents' => ['Agent', 403],
            'is forbiden for users' => ['User', 403],
        ];
    }

    public function storeInvalidData()
    {
        return [
            'missing label' => [
                [],
                ['label'],
            ],

            'label is not a stringl' => [
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
    public function test_store_a_tag(string $role, int $status)
    {
        $this->actingWithRole($role);
        $tag = Tag::factory()->make();

        $response = $this->post(route('api.v1.tags.store'), $tag->toArray())
            ->assertStatus($status);

        if ($status === 201) {
            $response->assertJson([
                'id' => 1,
                'label' => $tag->label,
            ]);
            $this->assertDatabaseCount('tags', 1);
        }

    }

    public function test_an_admin_store_a_tag_with_a_not_unique_name_used_in_a_soft_deleted_tag()
    {
        $this->actingWithRole('Admin');
        $tag = Tag::factory()->create();
        $tag->delete();

        $this->post(route('api.v1.tags.store'), $tag->toArray())
            ->assertStatus(201)
            ->assertJson([
                'id' => 2,
                'label' => $tag->label,
            ]);

        $this->assertDatabaseCount('tags', 2);
    }

    /**
     * @dataProvider storeInvalidData
     */
    public function test_an_admin_fails_store_a_tag_validation(array $data, array $fields)
    {
        $this->actingWithRole('Admin');
        Tag::factory()->create(['label' => 'not-unique']);

        $this->post(route('api.v1.tags.store'), $data)
            ->assertStatus(422)
            ->assertInvalid($fields);

        $this->assertDatabaseCount('tags', 1);
    }
}
