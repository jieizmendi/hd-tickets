<?php

namespace Tests\Feature\API\v1\Ticket;

use App\Models\Tag;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Arr;
use Tests\APITestCase;

class ReadAllTicketsTest extends APITestCase
{
    public function test_an_owner_read_all_his_tickets()
    {
        $user = $this->actingWithRole('User');
        Ticket::factory()->times(3)->create();
        Ticket::factory()->times(2)->create(['owner_id' => $user->id]);

        $this->get(route('api.v1.tickets.index', [
            'itemsPerPage' => 5,
        ]))
            ->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'total' => 2,
                ],
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_an_agent_read_all_tickets()
    {
        $agent = $this->actingWithRole('Agent');
        Ticket::factory()->times(3)->create();
        Ticket::factory()->times(2)->create(['owner_id' => $agent->id]);
        Ticket::factory()->times(4)->create()->each(function ($ticket) use ($agent) {
            $ticket->agents()->attach($agent);
        });

        $this->get(route('api.v1.tickets.index', [
            'itemsPerPage' => 5,
        ]))
            ->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'total' => 9,
                ],
            ])
            ->assertJsonCount(5, 'data');
    }

    public function test_an_agent_search_on_reading_all_tickets()
    {
        $this->actingWithRole('Agent');
        Ticket::factory()->times(3)->create();
        Ticket::factory()->create(['subject' => '-.-']);

        $this->get(route('api.v1.tickets.index', [
            'search' => "-.-",
        ]))
            ->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'total' => 1,
                ],
            ])
            ->assertJsonCount(1, 'data');
    }

    public function test_an_agent_sort_on_reading_all_tickets()
    {
        $this->actingWithRole('Agent');
        Ticket::factory()->create(['priority' => 5]);
        Ticket::factory()->create(['priority' => 6]);

        $this->get(route('api.v1.tickets.index', [
            'sortBy' => "priority",
            "sortDesc" => true,
        ]))
            ->assertStatus(200);
    }

    public function test_an_agent_filter_by_owner_on_reading_all_tickets()
    {
        $this->actingWithRole('Agent');
        $user = User::factory()->create();
        Ticket::factory()->times(3)->create(['owner_id' => $user->id]);
        Ticket::factory()->times(9)->create();

        $this->get(route('api.v1.tickets.index', [
            "owner" => $user->id,
        ]))
            ->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'total' => 3,
                ],
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_an_agent_filter_by_agent_on_reading_all_tickets()
    {
        $agent = $this->actingWithRole('Agent');
        Ticket::factory()->times(9)->create();
        Ticket::factory()->times(3)->create()->each(function ($ticket) use ($agent) {
            $ticket->agents()->attach($agent);
        });

        $this->get(route('api.v1.tickets.index', [
            "agent" => $agent->id,
        ]))
            ->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'total' => 3,
                ],
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_an_agent_filter_by_status_on_reading_all_tickets()
    {
        $agent = $this->actingWithRole('Agent');
        Ticket::factory()->times(3)->create();
        $ticket = Ticket::factory()->create();
        $ticket->statuses()->create([
            'name' => 'unique',
            'user_id' => $agent->id,
            'content' => 'some text',
        ]);

        $this->get(route('api.v1.tickets.index', [
            "status" => "unique",
        ]))
            ->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'total' => 1,
                ],
            ])
            ->assertJsonCount(1, 'data');
    }

    public function test_an_agent_filter_by_priority_on_reading_all_tickets()
    {
        $this->actingWithRole('Agent');
        Ticket::factory()->times(3)->create(['priority' => 999]);
        Ticket::factory()->times(9)->create();

        $this->get(route('api.v1.tickets.index', [
            "priority" => 999,
        ]))
            ->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'total' => 3,
                ],
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_an_agent_filter_by_any_tag_on_reading_all_tickets()
    {
        $this->actingWithRole('Agent');
        $tags = Tag::factory()->times(15)->create()->pluck('id')->toArray();
        $tag = Tag::factory()->create();

        Ticket::factory()->times(9)->create()
            ->each(function ($ticket) use ($tags) {
                $ticket->tag(Arr::random($tags, rand(0, 5)));
            });

        Ticket::factory()->times(3)->create()
            ->each(function ($ticket) use ($tags, $tag) {
                $ticket->tag(Arr::random($tags, rand(0, 5)));
                $ticket->tag($tag);
            });

        $this->get(route('api.v1.tickets.index', [
            "anyTag" => "{$tag->id},1001,1002",
        ]))
            ->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'total' => 3,
                ],
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_an_agent_filter_by_all_tags_on_reading_all_tickets()
    {
        $this->actingWithRole('Agent');
        $tags = Tag::factory()->times(15)->create()->pluck('id')->toArray();
        $filterTags = Tag::factory()->times(5)->create()->pluck('id')->toArray();

        Ticket::factory()->times(9)->create()
            ->each(function ($ticket) use ($tags, $filterTags) {
                $ticket->tag(Arr::random($tags, rand(0, 5)));
                $ticket->tag(Arr::random($filterTags, rand(0, 2)));
            });

        Ticket::factory()->times(3)->create()
            ->each(function ($ticket) use ($tags, $filterTags) {
                $ticket->tag(Arr::random($tags, rand(0, 5)));
                $ticket->tag($filterTags);
            });

        $this->get(route('api.v1.tickets.index', [
            "allTags" => implode(',', $filterTags),
        ]))
            ->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'total' => 3,
                ],
            ])
            ->assertJsonCount(3, 'data');
    }
}
