<?php

namespace Tests\Feature\API\v1\Auth;

use App\Events\Auth\LoggedOut;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\APITestCase;

class LogoutTest extends APITestCase
{
    public function test_an_user_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('random-device')->plainTextToken;

        // Confirm token existence
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => get_class($user),
        ]);

        $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$token}",
        ]);

        $this->post(route('api.v1.auth.logout'))
            ->assertStatus(204);

        // Confirm token remove
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => get_class($user),
        ]);
    }

    public function test_an_user_logout_delete_only_curent_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('random-device')->plainTextToken;
        $user->createToken('random-device-2')->plainTextToken;

        // Confirm tokens existence
        $this->assertDatabaseCount('personal_access_tokens', 2);

        $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$token}",
        ]);

        // Login out on first token
        $this->post(route('api.v1.auth.logout'))
            ->assertStatus(204);

        // Confirm that one token remains
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_an_unauthenticated_user_fails_to_logout()
    {
        $this->post(route('api.v1.auth.logout'))
            ->assertStatus(401);
    }

    public function test_it_dispatch_logged_out_on_logout()
    {
        Event::fake();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->post(route('api.v1.auth.logout'))
            ->assertStatus(204);

        Event::assertDispatched(LoggedOut::class);
    }
}
