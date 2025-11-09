<?php

namespace Database\Factories;

use App\Models\Publication;
use App\Models\User;
use App\Models\Category;
use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PublicationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Publication::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(4);
        $status = $this->faker->randomElement([
            "draft",
            "review",
            "published",
            "archived",
        ]);
        $publishedAt =
            $status === "published"
                ? $this->faker->dateTimeBetween("-1 month", "+1 month")
                : null;

        return [
            "title" => $title,
            "slug" => Str::slug($title),
            "summary" => $this->faker->paragraph(),
            "content_html" =>
                "<p>" . $this->faker->paragraphs(3, true) . "</p>",
            "category_id" => Category::factory(),
            "status" => $status,
            "published_at" => $publishedAt,
            "cover_media_id" => Media::factory(),
            "created_by" => User::factory(),
            "updated_by" => User::factory(),
            "featured" => $this->faker->boolean(),
            "soft_deleted" => false,
        ];
    }
}
