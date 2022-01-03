<?php

namespace Tests\Feature\API\v1\Ticket\File;

use App\Models\File;
use App\Models\Ticket;
use Tests\APITestCase;

class RemoveFilesFromTicketTest extends APITestCase
{
    public function test_an_admin_remove_a_file_from_a_ticket()
    {
        $this->actingWithRole('Admin');
        $ticket = Ticket::factory()->create();
        $file = File::factory()->create([
            'user_id' => $ticket->owner_id,
            'ticket_id' => $ticket->id,
        ]);

        $this->delete(route(
            'api.v1.tickets.files.remove',
            ['ticket' => $ticket->id, 'file' => $file->id]
        ))
            ->assertStatus(200)
            ->assertJsonMissing(['files' => [
                ['id' => $file->id],
            ]]);

        $this->assertSoftDeleted('files', ['id' => $file->id]);
    }

    public function test_a_ticket_agent_remove_owned_file_from_the_ticket()
    {
        $user = $this->actingWithRole('Agent');
        $ticket = Ticket::factory()->create();
        $ticket->agents()->attach($user);
        $file = File::factory()->create([
            'user_id' => $user->id,
            'ticket_id' => $ticket->id,
        ]);

        $this->delete(route(
            'api.v1.tickets.files.remove',
            ['ticket' => $ticket->id, 'file' => $file->id]
        ))
            ->assertStatus(200)
            ->assertJsonMissing(['files' => [
                ['id' => $file->id],
            ]]);

        $this->assertSoftDeleted('files', ['id' => $file->id]);
    }

    public function test_an_agent_fails_removing_not_owned_file_from_a_ticket()
    {
        $this->actingWithRole('Agent');
        $ticket = Ticket::factory()->create();
        $file = File::factory()->create([
            'user_id' => $ticket->owner_id,
            'ticket_id' => $ticket->id,
        ]);

        $this->delete(route(
            'api.v1.tickets.files.remove',
            ['ticket' => $ticket->id, 'file' => $file->id]
        ))
            ->assertStatus(403);

        $this->assertDatabaseHas('files', ['id' => $file->id, 'deleted_at' => null]);
    }

    public function test_a_ticket_owner_remove_one_owned_file_from_the_ticket()
    {
        $user = $this->actingWithRole('User');
        $ticket = Ticket::factory()->create(['owner_id' => $user->id]);
        $file = File::factory()->create([
            'user_id' => $user->id,
            'ticket_id' => $ticket->id,
        ]);

        $this->delete(route(
            'api.v1.tickets.files.remove',
            ['ticket' => $ticket->id, 'file' => $file->id]
        ))
            ->assertStatus(200)
            ->assertJsonMissing(['files' => [
                ['id' => $file->id],
            ]]);

        $this->assertSoftDeleted('files', ['id' => $file->id]);
    }

    public function test_a_ticket_owner_fails_removing_not_owned_file_from_the_ticket()
    {
        $user = $this->actingWithRole('User');
        $ticket = Ticket::factory()->create(['owner_id' => $user->id]);
        $file = File::factory()->create([
            //'user_id' => $ticket->owner_id,
            'ticket_id' => $ticket->id,
        ]);

        $this->delete(route(
            'api.v1.tickets.files.remove',
            ['ticket' => $ticket->id, 'file' => $file->id]
        ))
            ->assertStatus(403);

        $this->assertDatabaseHas('files', ['id' => $file->id, 'deleted_at' => null]);
    }
}
