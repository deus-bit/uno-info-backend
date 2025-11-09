<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(5);
        $startsAt = $this->faker->dateTimeBetween("+1 day", "+1 month");
        $endsAt = (clone $startsAt)->modify(
            "+" . $this->faker->numberBetween(1, 8) . " hours",
        );
        $status = $this->faker->randomElement([
            "draft",
            "review",
            "published",
            "archived",
        ]);

        return [
            "title" => $title,
            "slug" => Str::slug($title),
            "summary" => $this->faker->paragraph(),
            "content_html" =>
                "<p>" . $this->faker->paragraphs(3, true) . "</p>",
            "location" => $this->faker->address(),
            "starts_at" => $startsAt,
            "ends_at" => $endsAt,
            "status" => $status,
            "banner_media_id" => Media::factory(),
            "created_by" => User::factory(),
            "updated_by" => User::factory(),
            "soft_deleted" => false,
        ];
    }
}
