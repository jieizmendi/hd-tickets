<?php

namespace Tests\Feature\API\v1\Auth;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Tests\APITestCase;

class ProfileTest extends APITestCase
{
    public function updateProfileInvalidData()
    {
        return [
            'missing fields' => [
                [],
                ['email', 'name'],
            ],

            'missing name' => [
                [
                    'email' => 'valid@email.com',
                ],
                ['name'],
            ],

            'missing email' => [
                [
                    'name' => 'Random Mr. Dude',
                ],
                ['email'],
            ],

            'invalid email format' => [
                [
                    'email' => 'valid-email.com',
                    'name' => 'Random Mr. Dude',
                ],
                ['email'],
            ],

            'not unique email' => [
                [
                    'email' => 'not-unique@email.com',
                    'name' => 'Random Mr. Dude',
                ],
                ['email'],
            ],
        ];
    }

    public function changePasswordInvalidData()
    {
        return [
            'missing fields' => [
                [],
                ['password', 'new_password'],
            ],

            'missing password' => [
                [
                    'new_password' => 'passw.o_r!d1',
                    'new_password_confirmation' => 'passw.o_r!d1',
                ],
                ['password'],
            ],

            'missing new password' => [
                [
                    'password' => 'password',
                    'new_password_confirmation' => 'passw.o_r!d1',
                ],
                ['new_password'],
            ],

            'missing new password confirmation' => [
                [
                    'password' => 'password',
                    'new_password' => 'passw.o_r!d1',
                ],
                ['new_password'],
            ],

            'unmatching new password and confirmation' => [
                [
                    'password' => 'password',
                    'new_password' => 'passw.o_r!d1',
                    'new_password_confirmation' => 'passw.o_r!d',
                ],
                ['new_password'],
            ],

            'password too short' => [
                [
                    'password' => 'password',
                    'new_password' => 'pass',
                    'new_password_confirmation' => 'pass',
                ],
                ['new_password'],
            ],
        ];
    }

    public function test_an_user_retrives_profile()
    {
        $user = $this->actingWithRole("User");

        $this->get(route('api.v1.auth.profile.show'))
            ->assertOk()
            ->assertJson(array_intersect(
                $user->toArray(),
                ['id', 'name', 'email', 'role']
            ));
    }

    public function test_an_user_updates_profile()
    {
        $user = $this->actingWithRole('User');
        $raw = Arr::only(User::factory()->make()->toArray(), ['email', 'name']);

        $this->put(route('api.v1.auth.profile.update'), $raw)
            ->assertStatus(200)
            ->assertJson(array_intersect(
                $user->toArray(),
                ['id', 'name', 'email', 'role']
            ));

        $this->assertDatabaseHas('users', Arr::only($user->toArray(), ['id', 'name', 'email', 'role']));
    }

    /**
     * @dataProvider updateProfileInvalidData
     */
    public function test_an_user_fails_update_profile_validation(array $data, array $fields)
    {
        $this->actingWithRole('User');
        User::factory()->create(['email' => 'not-unique@email.com']);

        $this->put(route('api.v1.auth.profile.update'), $data)
            ->assertStatus(422)
            ->assertInvalid($fields);
    }

    public function test_an_user_change_password()
    {
        $user = $this->actingWithRole('User');

        $this->post(route('api.v1.auth.profile.change-password'), [
            'password' => 'password',
            'new_password' => 'passwo.r!d_1',
            'new_password_confirmation' => 'passwo.r!d_1',
        ])
            ->assertStatus(200)
            ->assertJson(array_intersect(
                $user->toArray(),
                ['id', 'name', 'email', 'role']
            ));

        $this->assertTrue(Hash::check('passwo.r!d_1', $user->fresh()->password));
    }

    /**
     * @dataProvider changePasswordInvalidData
     */
    public function test_an_user_fails_change_password_validation(array $data, array $fields)
    {
        $user = $this->actingWithRole('User');

        $this->post(route('api.v1.auth.profile.change-password'), $data)
            ->assertStatus(422)
            ->assertInvalid($fields);

        $this->assertFalse(Hash::check('passwo.r!d_1', $user->fresh()->password));
    }

    public function test_an_user_fails_to_change_password_with_the_wrong_current_password()
    {
        $user = $this->actingWithRole('User');

        $this->post(route('api.v1.auth.profile.change-password'), [
            'password' => 'password1',
            'new_password' => 'passwo.r!d_1',
            'new_password_confirmation' => 'passwo.r!d_1',
        ])
            ->assertStatus(400)
            ->assertJson(['message' => __('auth.password')]);

        $this->assertFalse(Hash::check('passwo.r!d_1', $user->fresh()->password));
    }
}
