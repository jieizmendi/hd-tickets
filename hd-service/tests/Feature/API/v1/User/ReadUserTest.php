<?php

namespace Tests\Feature\API\v1\User;

use App\Models\User;
use Tests\APITestCase;

class ReadUserTest extends APITestCase
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
    public function test_read_an_user(string $role, int $status)
    {
        $this->actingWithRole($role);
        $user = User::factory()->create();

        $response = $this->get(route('api.v1.users.show', $user->id))
            ->assertStatus($status);

        if ($status == 200) {
            $response->assertJson([
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]);
        }
    }
}
