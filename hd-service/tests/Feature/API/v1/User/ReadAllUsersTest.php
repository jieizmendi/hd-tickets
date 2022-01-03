<?php

namespace Tests\Feature\API\v1\User;

use App\Models\User;
use Tests\APITestCase;

class ReadAllUsersTest extends APITestCase
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
    public function test_read_all_users(string $role, int $status)
    {
        $this->actingWithRole($role);
        User::factory()->times(17)->create();

        $response = $this->get(route('api.v1.users.index', [
            'itemsPerPage' => 5,
        ]))
            ->assertStatus($status);

        if ($status == 200) {
            $response->assertJson([
                'meta' => [
                    'total' => 17 + 1, // 1 = Logged user.
                ],
            ])
                ->assertJsonCount(5, 'data');
        }
    }
}
