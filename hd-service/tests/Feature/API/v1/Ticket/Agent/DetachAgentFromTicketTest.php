<?php

namespace Tests\Feature\API\v1\Ticket\Agent;

use App\Models\Ticket;
use App\Models\User;
use Tests\APITestCase;

class DetachAgentFromTicketTest extends APITestCase
{
    public function externalDetachRolesAuthorizationData()
    {
        return [
            'is allowed for admins' => ['Admin', 200],
            'is forbiden for agents' => ['Agent', 403],
            'is forbiden for users' => ['User', 403],
        ];
    }

    public function selfDetachRoleAuthorizationData()
    {
        return [
            'is allowed for admins' => ['Admin', 200],
            'is allowed for agents' => ['Agent', 200],
            //'is forbiden for users' => ['User', 403],
        ];
    }

    /**
     * @dataProvider externalDetachRolesAuthorizationData
     */
    public function test_detach_agent_from_a_ticket(string $role, int $status)
    {
        $this->actingWithRole($role);
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create(['role' => 'Agent']);
        $ticket->agents()->attach($agent);

        $response = $this->delete(route('api.v1.tickets.agents.detach', [
            'ticket' => $ticket->id,
            'user' => $agent->id,
        ]))
            ->assertStatus($status);

        if ($status == 200) {
            $this->assertDatabaseMissing('ticket_agent', [
                'ticket_id' => $ticket->id,
                'agent_id' => $agent->id,
            ]);
            $response->assertJsonMissing(['agents' => [['id' => $agent->id]]]);
        } else {
            $this->assertDatabaseHas('ticket_agent', [
                'ticket_id' => $ticket->id,
                'agent_id' => $agent->id,
            ]);
        }
    }

    /**
     * @dataProvider selfDetachRoleAuthorizationData
     */
    public function test_detach_as_agent_from_a_ticket(string $role, int $status)
    {
        $agent = $this->actingWithRole($role);
        $ticket = Ticket::factory()->create();
        $ticket->agents()->attach($agent);

        $response = $this->delete(route('api.v1.tickets.agents.detach', [
            'ticket' => $ticket->id,
            'user' => $agent->id,
        ]))
            ->assertStatus($status);

        if ($status == 200) {
            $this->assertDatabaseMissing('ticket_agent', [
                'ticket_id' => $ticket->id,
                'agent_id' => $agent->id,
            ]);
            $response->assertJsonMissing(['agents' => [['id' => $agent->id]]]);
        } else {
            $this->assertDatabaseHas('ticket_agent', [
                'ticket_id' => $ticket->id,
                'agent_id' => $agent->id,
            ]);
        }
    }

    public function test_an_admin_detaching_non_attach_agent_to_the_ticket()
    {
        $this->actingWithRole('Admin');
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create(['role' => 'Agent']);

        $this->delete(route('api.v1.tickets.agents.detach', [
            'ticket' => $ticket->id,
            'user' => $agent->id,
        ]))
            ->assertStatus(400)
            ->assertJson(['message' => __('agents.non-attached', $agent->toArray())]);
    }
}
