<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

abstract class APITestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeaders([
            'Accept' => 'application/json',
        ]);
    }

    protected function actingWithRole(string $role): User
    {
        $user = User::factory()->role($role)->create();
        Sanctum::actingAs($user, ['*']);

        return $user;
    }
}
