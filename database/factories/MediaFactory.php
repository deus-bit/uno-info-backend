<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

class MediaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Media::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Ensure the public disk is faked for testing purposes
        if (app()->runningUnitTests()) {
            Storage::fake("public");
        }

        $fileName = $this->faker->word() . "." . $this->faker->fileExtension();
        $mimeType = $this->faker->mimeType();
        $sizeBytes = $this->faker->numberBetween(1000, 500000); // 1KB to 500KB

        // Create a dummy file in the fake storage
        $path = "media/" . $fileName;
        Storage::disk("public")->put(
            $path,
            $this->faker->text($sizeBytes / 10),
        ); // Dummy content

        return [
            "file_name" => $fileName,
            "mime_type" => $mimeType,
            "url" => Storage::disk("public")->url($path),
            "size_bytes" => $sizeBytes,
            "alt_text" => $this->faker->sentence(),
            "uploaded_by" => User::factory(),
        ];
    }
}
