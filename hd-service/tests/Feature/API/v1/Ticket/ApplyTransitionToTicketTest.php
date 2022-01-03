<?php

namespace Tests\Feature\API\v1\Ticket;

use App\Models\Ticket;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Tests\APITestCase;

class ApplyTransitionToTicketTest extends APITestCase
{
    use WithFaker;

    static $transitions = [
        'Open' => [
            'OnHold' => [
                'actor' => 'Agent',
                'actions' => [],
            ],
            'Pending' => [
                'actor' => 'Agent',
                'actions' => [
                    \App\Actions\NotifyOwnerThatActionIsNecessary::class,
                ],
            ],
            'Solved' => [
                'actor' => 'Agent',
                'actions' => [],
            ],
            'Closed' => [
                'actor' => 'Agent',
                'actions' => [],
            ],
        ],
        'OnHold' => [
            'Open' => [
                'actor' => 'Agent',
                'actions' => [],
            ],
            'Closed' => [
                'actor' => 'Agent',
                'actions' => [],
            ],
        ],
        'Pending' => [
            'Closed' => [
                'actor' => 'Agent',
                'actions' => [],
            ],
        ],
        'Solved' => [
            'Open' => [
                'actor' => 'Agent',
                'actions' => [],
            ],
        ],
        'Closed' => [
            'Open' => [
                'actor' => 'Agent',
                'actions' => [],
            ],
        ],
        'Pending' => [
            'Open' => [
                'actor' => 'Owner',
                'actions' => [],
            ],
        ],
        'Solved' => [
            'Open' => [
                'actor' => 'Owner',
                'actions' => [],
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // Enforce created assign action
        Config::set("hd.default.actions", [
            \App\Actions\AssignBestMatchedAgentAction::class,
        ]);
        Config::set("hd.default.status", "Open");

        // Enforce statuses
        Config::set("hd.statuses", ['Open', 'OnHold', 'Pending', 'Solved', 'Closed']);

        // Enforce transitions
        Config::set("hd.transitions", self::$transitions);
    }

    public function agentTransitionsData()
    {
        $ans = [];

        foreach (self::$transitions as $from => $toData) {
            foreach ($toData as $to => $data) {
                $ans["From {$from} to {$to} [" . $data['actor'] . "]"] = [
                    [
                        "from" => $from,
                        "to" => $to,
                    ],
                    $data['actor'] == 'Agent',
                ];
            }
        }

        return $ans;
    }

    public function ownerTransitionsData()
    {
        $ans = [];

        foreach (self::$transitions as $from => $toData) {
            foreach ($toData as $to => $data) {
                $ans["From {$from} to {$to} [" . $data['actor'] . "]"] = [
                    [
                        "from" => $from,
                        "to" => $to,
                    ],
                    $data['actor'] == 'Owner',
                ];
            }
        }

        return $ans;
    }

    public function transitionInvalidData()
    {
        return [
            'status missing' => [
                [],
                ['status'],
            ],

            'not valid status' => [
                [
                    'status' => 'not-a-valid-status',
                ],
                ['status'],
            ],

            'status is not string' => [
                [
                    'status' => [],
                ],
                ['status'],
            ],

            'content is not string' => [
                [
                    'status' => 'Open',
                    'content' => ['test'],
                ],
                ['content'],
            ],

            'content is longer than 1000 characters' => [
                [
                    'status' => 'Open',
                    'content' => str_repeat('a', 1001),
                ],
                ['content'],
            ],
        ];
    }

    /**
     * @dataProvider transitionInvalidData
     */
    public function test_an_agent_fails_validation_appling_a_transition(array $data, array $fields)
    {
        $this->actingWithRole('Agent');
        $ticket = Ticket::factory()->create();

        $this->post(route('api.v1.tickets.transition', $ticket->id), $data)
            ->assertStatus(422)
            ->assertInvalid($fields);
    }

    /**
     * @dataProvider agentTransitionsData
     */
    public function test_an_agent_apply_a_transition(array $data, bool $valid)
    {
        Event::fake();
        $agent = $this->actingWithRole('Agent');
        $ticket = Ticket::factory()->create();
        $ticket->agents()->attach($agent);
        $this->travel(5)->minutes(); // Status it's the latest one.
        $ticket->statuses()->create([
            'name' => $data['from'],
            'user_id' => $agent->id,
            'content' => $this->faker->sentence(),
        ]);

        $response = $this->post(
            route('api.v1.tickets.transition', $ticket->id),
            [
                'status' => $data['to'],
                'content' => $this->faker->sentence(),
            ]
        );

        if ($valid) {
            $response->assertStatus(200);

            Event::assertDispatched(\App\Events\Ticket\Transitioned::class);

            $this->assertDatabaseHas('statuses', [
                'user_id' => $agent->id,
                'name' => $data['to'],
                'ticket_id' => $ticket->id,
            ]);
        } else {
            $response->assertStatus(403);
        }
    }

    /**
     * @dataProvider ownerTransitionsData
     */
    public function test_an_owner_apply_a_transition(array $data, bool $valid)
    {
        Event::fake();
        $owner = $this->actingWithRole('User');
        $ticket = Ticket::factory()->create(['owner_id' => $owner->id]);
        $this->travel(5)->minutes(); // Status it's the latest one.
        $ticket->statuses()->create([
            'name' => $data['from'],
            'user_id' => $owner->id,
            'content' => $this->faker->sentence(),
        ]);
        $response = $this->post(
            route('api.v1.tickets.transition', $ticket->id),
            [
                'status' => $data['to'],
                'content' => $this->faker->sentence(),
            ]
        );

        if ($valid) {
            $response->assertStatus(200);

            Event::assertDispatched(\App\Events\Ticket\Transitioned::class);

            $this->assertDatabaseHas('statuses', [
                'user_id' => $owner->id,
                'name' => $data['to'],
                'ticket_id' => $ticket->id,
            ]);
        } else {
            $response->assertStatus(403);
        }
    }

    public function test_an_agent_fails_on_not_registered_transition()
    {
        $this->actingWithRole('Agent');
        $ticket = Ticket::factory()->create();

        $this->post(route('api.v1.tickets.transition', $ticket->id), [
            'status' => 'Open',
            'content' => $this->faker->sentence(),
        ])
            ->assertStatus(400)
            ->assertJson([
                'message' => __('statuses.invalid-transition', [
                    'from' => 'Open',
                    'to' => 'Open',
                ]),
            ]);
    }
}
