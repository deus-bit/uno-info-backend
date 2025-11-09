<?php

namespace Database\Factories;

use App\Models\Person;
use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Person::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "full_name" => $this->faker->name(),
            "position_title" => $this->faker->jobTitle(),
            "email" => $this->faker->unique()->safeEmail(),
            "phone" => $this->faker->phoneNumber(),
            "biography" => $this->faker->paragraph(),
            "photo_media_id" => Media::factory(),
        ];
    }
}
