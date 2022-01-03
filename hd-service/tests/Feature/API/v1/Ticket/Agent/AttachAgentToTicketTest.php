<?php

namespace Tests\Feature\API\v1\Ticket\Agent;

use App\Models\Ticket;
use App\Models\User;
use Tests\APITestCase;

class AttachAgentToTicketTest extends APITestCase
{
    public function externalAssigmentRolesAuthorizationData()
    {
        return [
            'is allowed for admins' => ['Admin', 200],
            'is forbiden for agents' => ['Agent', 403],
            'is forbiden for users' => ['User', 403],
        ];
    }

    public function selfAssigmentRoleAuthorizationData()
    {
        return [
            'is allowed for admins' => ['Admin', 200],
            'is allowed for agents' => ['Agent', 200],
            'is forbiden for users' => ['User', 403],
        ];
    }

    /**
     * @dataProvider externalAssigmentRolesAuthorizationData
     */
    public function test_attach_an_agent_to_a_ticket(string $role, int $status)
    {
        $this->actingWithRole($role);
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create(['role' => 'Agent']);

        $response = $this->post(route('api.v1.tickets.agents.attach', [
            'ticket' => $ticket->id,
            'user' => $agent->id,
        ]))
            ->assertStatus($status);

        if ($status == 200) {
            $this->assertDatabaseHas('ticket_agent', [
                'ticket_id' => $ticket->id,
                'agent_id' => $agent->id,
            ]);
            $response->assertJson(['agents' => [['id' => $agent->id]]]);
        } else {
            $this->assertDatabaseMissing('ticket_agent', [
                'ticket_id' => $ticket->id,
                'agent_id' => $agent->id,
            ]);
            $response->assertJsonMissing(['agents' => [['id' => $agent->id]]]);
        }
    }

    /**
     * @dataProvider selfAssigmentRoleAuthorizationData
     */
    public function test_attach_myself_as_an_agent_to_a_ticket(string $role, int $status)
    {
        $agent = $this->actingWithRole($role);
        $ticket = Ticket::factory()->create();

        $response = $this->post(route('api.v1.tickets.agents.attach', [
            'ticket' => $ticket->id,
            'user' => $agent->id,
        ]))
            ->assertStatus($status);

        if ($status == 200) {
            $this->assertDatabaseHas('ticket_agent', [
                'ticket_id' => $ticket->id,
                'agent_id' => $agent->id,
            ]);
            $response->assertJson(['agents' => [['id' => $agent->id]]]);
        } else {
            $this->assertDatabaseMissing('ticket_agent', [
                'ticket_id' => $ticket->id,
                'agent_id' => $agent->id,
            ]);
            $response->assertJsonMissing(['agents' => [['id' => $agent->id]]]);
        }
    }

    public function test_an_admin_fails_to_attach_repeated_agent_to_a_ticket()
    {
        $this->actingWithRole('Admin');
        $ticket = Ticket::factory()->create();
        $agent = User::factory()->create(['role' => 'Agent']);
        $ticket->agents()->attach($agent);

        $this->post(route('api.v1.tickets.agents.attach', [
            'ticket' => $ticket->id,
            'user' => $agent->id,
        ]))
            ->assertStatus(400)
            ->assertJson([
                'message' => __('agents.attached', $agent->toArray()),
            ]);
    }

    public function test_an_admin_fails_to_attach_an_user_to_a_ticket()
    {
        $this->actingWithRole('Admin');
        $ticket = Ticket::factory()->create();
        $user = User::factory()->create(['role' => 'User']);

        $this->post(route('api.v1.tickets.agents.attach', [
            'ticket' => $ticket->id,
            'user' => $user->id,
        ]))
            ->assertStatus(400)
            ->assertJson([
                'message' => __('users.not-agent', $user->toArray()),
            ]);
    }
}
