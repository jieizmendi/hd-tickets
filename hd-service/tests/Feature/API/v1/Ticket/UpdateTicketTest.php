<?php

namespace Tests\Feature\API\v1\Ticket;

use App\Models\Ticket;
use Illuminate\Support\Arr;
use Tests\APITestCase;

class UpdateTicketTest extends APITestCase
{
    public function rolesAuthorizationData()
    {
        return [
            'is forbiden for admins' => ['Admin', 403],
            'is forbiden for agents' => ['Agent', 403],
            'is forbiden for users' => ['User', 403],
        ];
    }

    public function updateInvalidData()
    {
        return [
            'missing fields' => [
                [],
                ['subject', 'content'],
            ],

            'missing subjetc' => [
                [
                    'content' => 'text',
                ],
                ['subject'],
            ],

            'missing content' => [
                [
                    'subject' => 'text',
                ],
                ['content'],
            ],

            'subject is less than 3 characters' => [
                [
                    'subject' => 'te',
                    'content' => 'text',
                ], ['subject'],
            ],

            'content is less than 3 characters' => [
                [
                    'subject' => 'text',
                    'content' => 'te',
                ],
                ['content'],
            ],

            'subject is more than 50 characters' => [
                [
                    'subject' => str_repeat('a', 51),
                    'content' => 'text',
                ],
                ['subject'],
            ],

            'content is more than 1000 characters' => [
                [
                    'subject' => 'text',
                    'content' => str_repeat('a', 1001),
                ],
                ['content'],
            ],

            'subject is not a string' => [
                [
                    'subject' => [],
                    'content' => 'text',
                ],
                ['subject'],
            ],

            'content is not a string' => [
                [
                    'subject' => 'text',
                    'content' => [],
                ],
                ['content'],
            ],
        ];
    }

    /**
     * @dataProvider rolesAuthorizationData
     */
    public function test_update_a_ticket(string $role, int $status)
    {
        $this->actingWithRole($role);
        $ticket = Ticket::factory()->create();
        $raw = Arr::only(Ticket::factory()->make()->toArray(), ['label']);

        $this->put(route('api.v1.tickets.update', $ticket->id), $raw)
            ->assertStatus($status);
    }

    public function test_an_user_update_an_owned_ticket()
    {
        $user = $this->actingWithRole('User');
        $ticket = Ticket::factory()->create(['owner_id' => $user->id]);

        $this->put(route('api.v1.tickets.update', $ticket->id), [
            'subject' => 'text-random-2',
            'content' => 'random-text-25',
        ])
            ->assertStatus(200);

        $this->assertDatabaseHas('tickets', [
            'subject' => 'text-random-2',
            'content' => 'random-text-25',
            'owner_id' => $user->id,
        ]);
    }

    /**
     * @dataProvider updateInvalidData
     */
    public function test_an_user_fails_to_update_an_owned_ticket(array $data, array $fields)
    {
        $user = $this->actingWithRole('User');
        $ticket = Ticket::factory()->create(['owner_id' => $user->id]);

        $this->put(route('api.v1.tickets.update', $ticket->id), $data)
            ->assertStatus(422)
            ->assertInvalid($fields);
    }
}
