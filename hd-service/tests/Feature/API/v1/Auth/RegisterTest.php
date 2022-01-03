<?php

namespace Tests\Feature\API\v1\Auth;

use App\Events\Auth\Registered;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\APITestCase;

class RegisterTest extends APITestCase
{
    public function signUpInvalidData()
    {
        return [
            'missing fields' => [
                [],
                ['name', 'email', 'password'],
            ],

            'missing name' => [
                [
                    'email' => 'mr@solomon.com',
                    'password' => 'passw.o_r!d1',
                    'password_confirmation' => 'passw.o_r!d1',
                ],
                ['name'],
            ],

            'missing email' => [
                [
                    'name' => 'Mr. Solomon',
                    'password' => 'passw.o_r!d1',
                    'password_confirmation' => 'passw.o_r!d1',
                ],
                ['email'],
            ],

            'missing password' => [
                [
                    'name' => 'Mr. Solomon',
                    'email' => 'mr@solomon.com',
                    'password_confirmation' => 'passw.o_r!d1',
                ],
                ['password'],
            ],

            'missing password confirmation' => [
                [
                    'name' => 'Mr. Solomon',
                    'email' => 'mr@solomon.com',
                    'password' => 'passw.o_r!d1',
                ],
                ['password'],
            ],

            'different password confirmation' => [
                [
                    'name' => 'Mr. Solomon',
                    'email' => 'mr@solomon.com',
                    'password' => 'passw.o_r!d1!',
                    'password_confirmation' => 'passw.o_r!d1',
                ],
                ['password'],
            ],

            'not unique email' => [
                [
                    'name' => 'Mr. Solomon',
                    'email' => 'not-unique@email.com',
                    'password' => 'passw.o_r!d1',
                    'password_confirmation' => 'passw.o_r!d1',
                ],
                ['email'],
            ],
        ];
    }

    public function test_a_guest_sign_up()
    {
        $this->post(route('api.v1.auth.register'), [
            'name' => 'Mr. Solomon',
            'email' => 'mr@solomon.com',
            'password' => 'passw.o_r!d1',
            'password_confirmation' => 'passw.o_r!d1',
        ])
            ->assertCreated()
            ->assertJson([
                'name' => 'Mr. Solomon',
                'email' => 'mr@solomon.com',
                'role' => 'User',
            ]);

        $user = User::where('email', 'mr@solomon.com')->first();

        $this->assertDatabaseHas('users', [
            'name' => 'Mr. Solomon',
            'email' => 'mr@solomon.com',
            'role' => 'User',
        ]);

        $this->assertFalse(Hash::check('passwo.r!d_1', $user->password));
    }

    /**
     * @dataProvider signUpInvalidData
     */
    public function test_a_guest_fails_sign_up_validation(array $data, array $fields)
    {
        User::factory()->create(['email' => 'not-unique@email.com']);

        $this->post(route('api.v1.auth.register'), $data)
            ->assertStatus(422)
            ->assertInvalid($fields);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_an_user_fails_sign_up()
    {
        $this->actingWithRole('User');

        $this->post(route('api.v1.auth.register'), [
            'name' => 'Mr. Solomon',
            'email' => 'mr@solomon.com',
            'password' => 'passw.o_r!d1',
            'password_confirmation' => 'passw.o_r!d1',
        ])
            ->assertStatus(409);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_it_dispatch_registered_event_on_sing_up()
    {
        Event::fake();
        $user = User::factory()->create();

        $this->post(route('api.v1.auth.register'), [
            'name' => 'Mr. Solomon',
            'email' => 'mr@solomon.com',
            'password' => 'passw.o_r!d1',
            'password_confirmation' => 'passw.o_r!d1',
        ])
            ->assertCreated();

        Event::assertDispatched(Registered::class);
    }
}
