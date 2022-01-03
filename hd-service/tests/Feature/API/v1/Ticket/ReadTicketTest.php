<?php

namespace Tests\Feature\API\v1\Ticket;

use App\Models\Ticket;
use Tests\APITestCase;

class ReadTicketTest extends APITestCase
{
    public function rolesAuthorizationData()
    {
        return [
            'is allowed for admins' => ['Admin', 200],
            'is allowed for agents' => ['Agent', 200],
            'is forbiden for users' => ['User', 403],
        ];
    }

    /**
     * @dataProvider rolesAuthorizationData
     */
    public function test_read_a_ticket(string $role, int $status)
    {
        $this->actingWithRole($role);
        $ticket = Ticket::factory()->create();

        $this->get(route('api.v1.tickets.show', $ticket->id))
            ->assertStatus($status);
    }

    public function test_an_user_reads_owned_ticket()
    {
        $user = $this->actingWithRole('User');
        $ticket = Ticket::factory()->create(['owner_id' => $user->id]);

        $this->get(route('api.v1.tickets.show', $ticket->id))
            ->assertStatus(200);
    }
}
