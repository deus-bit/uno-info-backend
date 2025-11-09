<?php

namespace Database\Factories;

use App\Models\Form;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Form::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true) . " Form";
        $code = $this->faker->unique()->slug(3);

        return [
            "name" => $name,
            "code" => $code,
            "schema_json" => json_encode([
                "fields" => [
                    [
                        "name" => "field1",
                        "type" => "string",
                        "required" => true,
                        "label" => "Field One",
                    ],
                    [
                        "name" => "field2",
                        "type" => "email",
                        "required" => false,
                        "label" => "Field Two",
                    ],
                ],
                "settings" => [
                    "recaptcha" => true,
                ],
            ]),
            "is_active" => $this->faker->boolean(),
            "created_by" => User::factory(),
        ];
    }
}
