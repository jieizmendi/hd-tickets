<?php

namespace Tests\Feature\API\v1\Ticket;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Tests\APITestCase;

class StoreTicketTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set("hd.priorities", 10);
        Config::set("hd.default", [
            'status' => 'Open',
            'priority' => 5,

            // Default Actions will happen upon creation
            'actions' => [
                \App\Actions\AssignBestMatchedAgentAction::class,
            ],
        ]);
    }

    public function rolesAuthorizationData()
    {
        return [
            'is allowed for admins' => ['Admin', 201],
            'is allowed for agents' => ['Agent', 201],
            'is allowed for users' => ['User', 201],
        ];
    }

    public function storeInvalidData()
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
                ],
                ['subject'],
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
    public function test_store_a_ticket(string $role, int $status)
    {
        $user = $this->actingWithRole($role);
        $data = [
            'subject' => 'subject-text',
            'content' => 'content-text',
        ];

        $response = $this->post(route('api.v1.tickets.store'), $data)
            ->assertStatus($status);

        if ($status === 201) {
            $response->assertJson([
                'subject' => 'subject-text',
                'content' => 'content-text',
                'owner' => ['id' => $user->id],
                'agents' => [],
                'status' => config('hd.default.status'),
                'files' => [],
                'statuses' => [
                    [
                        'name' => config('hd.default.status'),
                        'user' => ['id' => $user->id],
                    ],
                ],
            ]);

            $this->assertDatabaseCount('tickets', 1);
            $this->assertDatabaseCount('statuses', 1);
        }
    }

    /**
     * @dataProvider storeInvalidData
     */
    public function test_an_user_fails_to_store_a_ticket(array $data, array $fields)
    {
        $this->actingWithRole('User');

        $this->post(route('api.v1.tickets.store'), $data)
            ->assertStatus(422)
            ->assertInvalid($fields);

        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_an_user_store_a_ticket_with_file()
    {
        $user = $this->actingWithRole('User');
        $data = [
            'subject' => 'subject-text',
            'content' => 'content-text',
            'files' => [
                ['file' => UploadedFile::fake()->image('photo1.jpg')],
            ],
        ];

        $this->post(route('api.v1.tickets.store'), $data)
            ->assertStatus(201)
            ->assertJson([
                'subject' => 'subject-text',
                'content' => 'content-text',
                'owner' => ['id' => $user->id],
                'agents' => [],
                'status' => config('hd.default.status'),
                'files' => [
                    ['name' => 'photo1.jpg'],
                ],
                'statuses' => [
                    [
                        'name' => config('hd.default.status'),
                        'user' => ['id' => $user->id],
                    ],
                ],
            ]);

        $this->assertDatabaseCount('tickets', 1);
        $this->assertDatabaseCount('statuses', 1);
        $this->assertDatabaseCount('files', 1);
    }

    public function test_an_user_store_a_ticket_and_an_agent_its_attached()
    {
        $owner = $this->actingWithRole('User');
        $data = [
            'subject' => 'subject-text',
            'content' => 'content-text',
        ];
        $agent = User::factory()->role('Agent')->create();

        $this->post(route('api.v1.tickets.store'), $data)
            ->assertStatus(201);

        $ticket = Ticket::where('owner_id', $owner->id)->first();
        $this->assertTrue($ticket->agents()->count() == 1);
    }
}
