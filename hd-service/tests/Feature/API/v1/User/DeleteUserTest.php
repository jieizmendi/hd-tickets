<?php

namespace Tests\Feature\API\v1\User;

use App\Models\User;
use Tests\APITestCase;

class DeleteUserTest extends APITestCase
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
    public function test_delete_an_user(string $role, int $status)
    {
        $this->actingWithRole($role);
        $user = User::factory()->create();

        $this->delete(route('api.v1.users.destroy', $user->id))
            ->assertStatus($status);

        if ($status == 204) {
            $this->assertSoftDeleted('users', ['id' => $user->id]);
        }
    }
}
