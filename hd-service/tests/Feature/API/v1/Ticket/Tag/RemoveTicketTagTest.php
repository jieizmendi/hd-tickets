<?php

namespace Tests\Feature\API\v1\Ticket\Tag;

use App\Models\Tag;
use App\Models\Ticket;
use Tests\APITestCase;

class RemoveTicketTagTest extends APITestCase
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
    public function test_remove_a_tag_from_a_ticket(string $role, int $status)
    {
        $this->actingWithRole($role);
        $ticket = Ticket::factory()->create();
        $tag = Tag::factory()->create();
        $ticket->tag($tag);

        $this->delete(route('api.v1.tickets.tags.remove', [
            'ticket' => $ticket->id,
            'tag' => $tag->id,
        ]))
            ->assertStatus($status);

        if ($status == 200) {
            $this->assertDatabaseMissing('taggables', [
                'taggable_type' => Ticket::class,
                'taggable_id' => $ticket->id,
                'tag_id' => $tag->id,
            ]);
        } else {
            $this->assertDatabaseHas('taggables', [
                'taggable_type' => Ticket::class,
                'taggable_id' => $ticket->id,
                'tag_id' => $tag->id,
            ]);
        }
    }

    public function test_an_owner_removes_a_tag_from_the_ticket()
    {
        $user = $this->actingWithRole('User');
        $ticket = Ticket::factory()->create(['owner_id' => $user->id]);
        $tag = Tag::factory()->create();
        $ticket->tag($tag);

        $this->delete(route('api.v1.tickets.tags.remove', [
            'ticket' => $ticket->id,
            'tag' => $tag->id,
        ]))
            ->assertStatus(200);

        $this->assertDatabaseMissing('taggables', [
            'taggable_type' => Ticket::class,
            'taggable_id' => $ticket->id,
            'tag_id' => $tag->id,
        ]);
    }
}
