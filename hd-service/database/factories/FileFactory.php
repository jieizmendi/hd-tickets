<?php

namespace Database\Factories;

use App\Models\File;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = File::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->uuid(),
            'location' => $this->faker->fileExtension(),
            'is_public' => $this->faker->boolean(),
            'user_id' => User::factory(),
            'ticket_id' => Ticket::factory(),
        ];
    }
}
