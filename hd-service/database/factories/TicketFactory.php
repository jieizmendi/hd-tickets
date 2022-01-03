<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Ticket::class;

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Ticket $ticket) {
            $ticket->statuses()->create([
                'name' => config('hd.default.status'),
                'user_id' => $ticket->owner_id,
            ]);
        });
    }

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'subject' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'owner_id' => User::factory(),
            'priority' => $this->faker->numberBetween(0, config('hd.priorities', 10)),
        ];
    }
}
