<?php

namespace Database\Factories;

use App\Models\Program;
use App\Models\Faculty;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProgramFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Program::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true) . " Program";
        return [
            "faculty_id" => Faculty::factory(),
            "name" => $name,
            "slug" => Str::slug($name),
            "degree_type" => $this->faker->randomElement([
                "Bachelor",
                "Master",
                "PhD",
            ]),
            "duration_semesters" => $this->faker->numberBetween(4, 12),
            "modality" => $this->faker->randomElement([
                "Presencial",
                "Virtual",
                "Mixed",
            ]),
            "description" => $this->faker->paragraph(),
        ];
    }
}
