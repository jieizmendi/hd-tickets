<?php

namespace Tests\Feature\API\v1\Ticket\File;

use App\Models\Ticket;
use Illuminate\Http\UploadedFile;
use Tests\APITestCase;

class AddFilesToTicketTest extends APITestCase
{
    private function rawFilesData()
    {
        return [
            'files' => [
                [
                    'file' => UploadedFile::fake()->image('public_1.jpg'),
                ],
                [
                    'file' => UploadedFile::fake()->image('public_2.jpg'),
                    'is_public' => true,
                ],
                [
                    'file' => UploadedFile::fake()->image('private.jpg'),
                    'is_public' => false,
                ],
            ],
        ];
    }

    public function test_an_admin_add_files_to_a_ticket()
    {
        $user = $this->actingWithRole('Admin');
        $ticket = Ticket::factory()->create();

        $this->post(
            route('api.v1.tickets.files.add', $ticket->id),
            $this->rawFilesData()
        )
            ->assertStatus(200)
            ->assertJson(['files' => [
                ['name' => 'public_1.jpg'],
                ['name' => 'public_2.jpg'],
                ['name' => 'private.jpg'],
            ]]);

        $this->assertDatabaseHas('files', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'name' => 'public_1.jpg',
            'is_public' => true,
        ]);
        $this->assertDatabaseHas('files', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'name' => 'public_2.jpg',
            'is_public' => true,
        ]);
        $this->assertDatabaseHas('files', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'name' => 'private.jpg',
            'is_public' => false,
        ]);
    }

    public function test_a_ticket_agent_add_files_to_the_ticket()
    {
        $user = $this->actingWithRole('Agent');
        $ticket = Ticket::factory()->create();
        $ticket->agents()->attach($user);

        $this->post(
            route('api.v1.tickets.files.add', $ticket->id),
            $this->rawFilesData()
        )
            ->assertStatus(200)
            ->assertJson(['files' => [
                ['name' => 'public_1.jpg'],
                ['name' => 'public_2.jpg'],
                ['name' => 'private.jpg'],
            ]]);

        $this->assertDatabaseHas('files', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'name' => 'public_1.jpg',
            'is_public' => true,
        ]);
        $this->assertDatabaseHas('files', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'name' => 'public_2.jpg',
            'is_public' => true,
        ]);
        $this->assertDatabaseHas('files', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'name' => 'private.jpg',
            'is_public' => false,
        ]);
    }

    public function test_an_agent_fails_to_add_files_to_a_ticket()
    {
        $user = $this->actingWithRole('Agent');
        $ticket = Ticket::factory()->create();

        $this->post(
            route('api.v1.tickets.files.add', $ticket->id),
            $this->rawFilesData()
        )
            ->assertStatus(403);

        $this->assertDatabaseMissing('files', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'name' => 'public_1.jpg',
            'is_public' => true,
        ]);
        $this->assertDatabaseMissing('files', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'name' => 'public_2.jpg',
            'is_public' => true,
        ]);
        $this->assertDatabaseMissing('files', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'name' => 'private.jpg',
            'is_public' => false,
        ]);
    }

    public function test_a_ticket_owner_add_files_to_the_ticket()
    {
        $user = $this->actingWithRole('User');
        $ticket = Ticket::factory()->create(['owner_id' => $user->id]);
        $data = [
            'files' => [
                [
                    'file' => UploadedFile::fake()->image('public.jpg'),
                ],
            ],
        ];

        $this->post(route('api.v1.tickets.files.add', $ticket->id), $data)
            ->assertStatus(200)
            ->assertJson(['files' => [
                ['name' => 'public.jpg'],
            ]]);

        $this->assertDatabaseHas('files', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'name' => 'public.jpg',
            'is_public' => true,
        ]);
    }

    public function test_a_ticket_owner_add_a_private_file_to_the_ticket_and_private_setting_is_ignored()
    {
        $user = $this->actingWithRole('User');
        $ticket = Ticket::factory()->create(['owner_id' => $user->id]);
        $data = [
            'files' => [
                [
                    'file' => UploadedFile::fake()->image('public.jpg'),
                    'is_public' => false,
                ],
            ],
        ];

        $this->post(route('api.v1.tickets.files.add', $ticket->id), $data)
            ->assertStatus(200)
            ->assertJson(['files' => [
                ['name' => 'public.jpg'],
            ]]);

        $this->assertDatabaseHas('files', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'name' => 'public.jpg',
            'is_public' => true,
        ]);
    }

    public function test_an_user_fails_to_add_a_file_to_a_ticket()
    {
        $user = $this->actingWithRole('User');
        $ticket = Ticket::factory()->create();

        $this->post(
            route('api.v1.tickets.files.add', $ticket->id),
            $this->rawFilesData()
        )
            ->assertStatus(403);

        $this->assertDatabaseMissing('files', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'name' => 'public_1.jpg',
            'is_public' => true,
        ]);
        $this->assertDatabaseMissing('files', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'name' => 'public_2.jpg',
            'is_public' => true,
        ]);
        $this->assertDatabaseMissing('files', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'name' => 'private.jpg',
            'is_public' => false,
        ]);
    }

}
