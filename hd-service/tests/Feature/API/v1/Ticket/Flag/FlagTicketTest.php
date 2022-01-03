<?php

namespace Tests\Feature\API\v1\Ticket\Flag;

use App\Models\Ticket;
use Illuminate\Support\Arr;
use Tests\APITestCase;

class FlagTicketTest extends APITestCase
{
    public function test_an_owner_flags_the_ticket()
    {
        $user = $this->actingWithRole('User');
        $ticket = Ticket::factory()->create(['owner_id' => $user->id]);
        $flag = Arr::random(config('hd.flags'));

        $this->post(route('api.v1.tickets.flags.create', [
            'ticket' => $ticket->id,
            'flag' => $flag,
        ]))
            ->assertStatus(200);

        $this->assertDatabaseHas('flags', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'flag' => $flag,
        ]);
    }

    public function test_an_user_fails_to_use_same_flag()
    {
        $user = $this->actingWithRole('User');
        $ticket = Ticket::factory()->create(['owner_id' => $user->id]);
        $flag = Arr::random(config('hd.flags'));
        $ticket->flags()->create([
            'user_id' => $user->id,
            'flag' => $flag,
        ]);

        $this->post(route('api.v1.tickets.flags.create', [
            'ticket' => $ticket->id,
            'flag' => $flag,
        ]))
            ->assertStatus(400);
    }

    public function test_an_owner_fails_to_flag_the_ticket_with_invalid_flag()
    {
        $user = $this->actingWithRole('User');
        $ticket = Ticket::factory()->create(['owner_id' => $user->id]);
        $flag = 'not-valid-flag';

        $this->post(route('api.v1.tickets.flags.create', [
            'ticket' => $ticket->id,
            'flag' => $flag,
        ]))
            ->assertStatus(422)
            ->assertInvalid('flag');

        $this->assertDatabaseMissing('flags', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'flag' => $flag,
        ]);
    }

    public function test_an_agent_flags_a_ticket()
    {
        $user = $this->actingWithRole('Agent');
        $ticket = Ticket::factory()->create();
        $flag = Arr::random(config('hd.flags'));

        $this->post(route('api.v1.tickets.flags.create', [
            'ticket' => $ticket->id,
            'flag' => $flag,
        ]))
            ->assertStatus(200);

        $this->assertDatabaseHas('flags', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'flag' => $flag,
        ]);
    }
}
