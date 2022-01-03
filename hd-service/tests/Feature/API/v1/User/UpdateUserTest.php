<?php

namespace Tests\Feature\API\v1\User;

use App\Models\User;
use Illuminate\Support\Arr;
use Tests\APITestCase;

class UpdateUserTest extends APITestCase
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
            'missing role' => [
                [],
                ['role'],
            ],

            'role is not listed' => [
                [
                    'role' => 'not-a-valid-role',
                ],
                ['role'],
            ],
        ];
    }

    /**
     * @dataProvider rolesAuthorizationData
     */
    public function test_update_an_user(string $role, int $status)
    {
        $this->actingWithRole($role);
        $user = User::factory()->create();
        $raw = ['role' => Arr::random(config('hd.roles'))];

        $response = $this->put(route('api.v1.users.update', $user->id), $raw)
            ->assertStatus($status);

        if ($status === 200) {
            $response->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $raw['role'],
            ]);

            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $raw['role'],
            ]);
        }
    }

    /**
     * @dataProvider updateInvalidData
     */
    public function test_an_admin_fails_to_update_an_user(array $data, array $fields)
    {
        $this->actingWithRole('Admin');
        $user = User::factory()->create();

        $this->put(route('api.v1.users.update', $user->id), $data)
            ->assertStatus(422)
            ->assertInvalid($fields);
    }
}
