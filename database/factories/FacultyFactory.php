<?php

namespace Database\Factories;

use App\Models\Faculty;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FacultyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Faculty::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->company() . " Faculty";
        return [
            "name" => $name,
            "slug" => Str::slug($name),
            "description" => $this->faker->paragraph(),
            "dean_person_id" => Person::factory(),
        ];
    }
}
