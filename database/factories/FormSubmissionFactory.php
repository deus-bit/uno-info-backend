<?php

namespace Database\Factories;

use App\Models\FormSubmission;
use App\Models\Form;
use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormSubmissionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FormSubmission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "form_id" => Form::factory(),
            "submitted_at" => $this->faker->dateTimeBetween("-1 year", "now"),
            "ip_address" => $this->faker->ipv4(),
            "payload_json" => json_encode([
                "name" => $this->faker->name(),
                "email" => $this->faker->unique()->safeEmail(),
                "message" => $this->faker->paragraph(),
            ]),
            "attachment_media_id" => Media::factory(),
        ];
    }
}
