<?php

namespace Tests\Feature\API\v1\Auth;

use App\Events\Auth\LoggedIn;
use App\Events\Auth\LoginAttempt;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tests\APITestCase;

class LoginTest extends APITestCase
{
    public function loginInvalidData()
    {
        return [
            'missing fields' => [
                [],
                ['email', 'password', 'device'],
            ],

            'missing password' => [
                [
                    'email' => 'valid@email.com',
                    'device' => 'test-device',
                ],
                ['password'],
            ],

            'missing email' => [
                [
                    'password' => 'password',
                    'device' => 'test-device',
                ],
                ['email'],
            ],

            'missing device' => [
                [
                    'email' => 'valid@email.com',
                    'password' => 'password',
                ],
                ['device'],
            ],

            'invalid email format' => [
                [
                    'email' => 'valid-email.com',
                    'password' => 'password',
                    'device' => 'test-device',
                ],
                ['email'],
            ],
        ];
    }

    public function test_a_guest_sing_in()
    {
        $user = User::factory()->create();

        $this->post(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => 'password',
            'device' => 'test-suit',
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'type',
            ]);
    }

    public function test_an_user_fails_signing_in()
    {
        $this->actingWithRole('User');
        $user = User::factory()->create();

        $this->post(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => 'password',
            'device' => 'test-suit',
        ])
            ->assertStatus(409);
    }

    /**
     * @dataProvider loginInvalidData
     */
    public function test_a_guest_fails_sign_in_validation(array $data, array $fields)
    {
        $this->post(route('api.v1.auth.login'), $data)
            ->assertStatus(422)
            ->assertInvalid($fields);

        $this->assertGuest();
    }

    public function test_a_guest_fails_signing_in_with_the_wrong_password()
    {
        $user = User::factory()->create();

        $this->post(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
            'device' => 'test-suit',
        ])
            ->assertStatus(401)
            ->assertJson(['message' => __('auth.failed')]);

        $this->assertGuest();
    }

    public function test_a_guest_fails_signing_in_with_the_wrong_email()
    {
        User::factory()->create();

        $user = User::factory()->create([
            'password' => bcrypt('password1'),
        ]);

        $this->post(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => 'password',
            'device' => 'test-suit',
        ])
            ->assertStatus(401)
            ->assertJson(['message' => __('auth.failed')]);

        $this->assertGuest();
    }

    public function test_it_dispatch_login_attempt_and_logged_in_events_on_succesfull_sing_in()
    {
        Event::fake();
        $user = User::factory()->create();

        $this->post(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => 'password',
            'device' => 'test-suit',
        ])->assertOk();

        Event::assertDispatched(LoginAttempt::class);
        Event::assertDispatched(LoggedIn::class);
    }

    public function test_it_dispatch_login_attempt_event_on_wrong_credentials_sing_in()
    {
        Event::fake();
        $user = User::factory()->create();

        $this->post(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => 'password1',
            'device' => 'test-suit',
        ])->assertStatus(401);

        Event::assertDispatched(LoginAttempt::class);
        Event::assertNotDispatched(LoggedIn::class);
    }
}
