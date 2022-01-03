<?php

namespace Tests\Feature\API\v1\Ticket\Flag;

use App\Models\Ticket;
use Illuminate\Support\Arr;
use Tests\APITestCase;

class UnflagTicketTest extends APITestCase
{
    public function test_an_user_deletes_a_flag()
    {
        $user = $this->actingWithRole('User');
        $ticket = Ticket::factory()->create();
        $flag = Arr::random(config('hd.flags'));
        $ticket->flags()->create([
            'user_id' => $user->id,
            'flag' => $flag,
        ]);

        $this->delete(route('api.v1.tickets.flags.remove', [
            'ticket' => $ticket->id,
            'flag' => $flag,
        ]))
            ->assertStatus(200);

        $this->assertSoftDeleted('flags', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'flag' => $flag,
        ]);
    }

    public function test_an_user_fails_to_remove_a_missing_flag()
    {
        $this->actingWithRole('User');
        $ticket = Ticket::factory()->create();
        $flag = Arr::random(config('hd.flags'));

        $this->delete(route('api.v1.tickets.flags.remove', [
            'ticket' => $ticket->id,
            'flag' => $flag,
        ]))
            ->assertStatus(400);
    }
}
