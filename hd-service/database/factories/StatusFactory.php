<?php

namespace Database\Factories;

use App\Models\Status;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Status::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => config('hd.default.status', 'New'),
            'content' => $this->faker->sentence(),
            'is_public' => $this->faker->boolean(),
            'user_id' => User::factory(),
            'ticket_id' => Ticket::factory(),
        ];
    }
}
