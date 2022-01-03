<?php

namespace Tests\Feature\API\v1;

use App\Models\Tag;
use App\Models\Ticket;
use App\Models\User;
use Tests\APITestCase;

class MetricsTest extends APITestCase
{
    public function test_user_retrives_metrics()
    {
        $user = $this->actingWithRole('User');

        $tags = Tag::factory()->times(5)->create();
        $ticket = Ticket::factory()->create([
            'owner_id' => $user->id,
            'priority' => 6,
        ]);
        $ticket->tag($tags[0]->id);
        $ticket->tag($tags[1]->id);

        $ticket = Ticket::factory()->create([
            'owner_id' => $user->id,
            'priority' => 6,
        ]);
        $ticket->tag($tags[1]->id);
        $ticket->tag($tags[2]->id);

        $ticket = Ticket::factory()->create([
            'owner_id' => $user->id,
            'priority' => 8,

        ]);
        $ticket->tag($tags[2]->id);
        $ticket->tag($tags[3]->id);
        $this->travel(5)->minutes();
        $ticket->statuses()->create([
            'name' => 'OnHold',
            'content' => 'some-text',
            'user_id' => User::factory()->role('Agent')->create()->id,
        ]);

        $this->get(route('api.v1.metrics'))
            ->assertStatus(200)
            ->assertJson([
                'count_per_priority' => [
                    6 => 2,
                    8 => 1,
                ],
                'count_per_tag' => [
                    $tags[0]->id => 1,
                    $tags[1]->id => 2,
                    $tags[2]->id => 2,
                    $tags[3]->id => 1,
                ],
                'count_per_current_status' => [
                    'OnHold' => 1,
                    'Open' => 2,
                ],
            ]);
    }

    public function test_agent_retrives_metrics()
    {
        $agent = $this->actingWithRole('Agent');
        $tags = Tag::factory()->times(5)->create();

        $ticket = Ticket::factory()->create([
            'priority' => 6,
        ]);
        $ticket->tag($tags[0]->id);
        $ticket->tag($tags[1]->id);
        $ticket->agents()->attach($agent);

        $ticket = Ticket::factory()->create([
            'priority' => 6,
        ]);
        $ticket->tag($tags[1]->id);
        $ticket->tag($tags[2]->id);
        $this->travel(1)->minutes();
        $ticket->statuses()->create([
            'name' => 'Closed',
            'content' => 'some-text',
            'user_id' => $agent->id,
        ]);
        $ticket->agents()->attach($agent);

        $ticket = Ticket::factory()->create([
            'priority' => 8,
        ]);
        $ticket->tag($tags[2]->id);
        $ticket->tag($tags[3]->id);
        $this->travel(5)->minutes();
        $ticket->statuses()->create([
            'name' => 'Solved',
            'content' => 'some-text',
            'user_id' => $agent->id,
        ]);
        $ticket->agents()->attach($agent);

        $this->get(route('api.v1.metrics'))
            ->assertStatus(200)
            ->assertJson([
                'count_per_priority' => [
                    6 => 2,
                    8 => 1,
                ],
                'count_per_tag' => [
                    $tags[0]->id => 1,
                    $tags[1]->id => 2,
                    $tags[2]->id => 2,
                    $tags[3]->id => 1,
                ],
                'count_per_current_status' => [
                    'Open' => 1,
                    'Solved' => 1,
                    'Closed' => 1,
                ],
                'avg_time_to_end_status' => (5 * 60 + 1 * 60) / 2,
            ]);
    }

    public function test_admin_retrives_metrics()
    {
        $this->actingWithRole('Admin');
        $tags = Tag::factory()->times(5)->create();
        $agents = User::factory()->times(2)->role('Agent')->create();

        $ticket = Ticket::factory()->create([
            'priority' => 6,
        ]);
        $ticket->tag($tags[0]->id);
        $ticket->tag($tags[1]->id);
        $this->travel(2)->minutes();
        $ticket->statuses()->create([
            'name' => 'Closed',
            'content' => 'some-text',
            'user_id' => $agents[1]->id,
        ]);
        $ticket->agents()->attach($agents[0]);

        $ticket = Ticket::factory()->create([
            'priority' => 6,
        ]);
        $ticket->tag($tags[1]->id);
        $ticket->tag($tags[2]->id);
        $this->travel(1)->minutes();
        $ticket->statuses()->create([
            'name' => 'Closed',
            'content' => 'some-text',
            'user_id' => $agents[1]->id,
        ]);
        $ticket->agents()->attach($agents[1]);

        $ticket = Ticket::factory()->create([
            'priority' => 8,
        ]);
        $ticket->tag($tags[2]->id);
        $ticket->tag($tags[3]->id);
        $this->travel(5)->minutes();
        $ticket->statuses()->create([
            'name' => 'Solved',
            'content' => 'some-text',
            'user_id' => $agents[1]->id,
        ]);
        $ticket->agents()->attach($agents[1]);

        $this->get(route('api.v1.metrics'))
            ->assertStatus(200)
            ->assertJson([
                'count_per_priority' => [
                    6 => 2,
                    8 => 1,
                ],
                'count_per_tag' => [
                    $tags[0]->id => 1,
                    $tags[1]->id => 2,
                    $tags[2]->id => 2,
                    $tags[3]->id => 1,
                ],
                'count_per_current_status' => [
                    'Solved' => 1,
                    'Closed' => 2,
                ],
                'avg_time_to_end_status' => (5 * 60 + 1 * 60 + 60 * 2) / 3,
                'avg_time_to_end_status_per_agent' => [
                    $agents[0]->id => 2 * 60,
                    $agents[1]->id => (5 * 60 + 1 * 60) / 2,
                ],
            ]);
    }
}
