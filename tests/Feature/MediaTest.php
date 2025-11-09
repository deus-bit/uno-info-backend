<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake("public"); // Use a fake disk for testing file uploads

        $permission = Permission::firstOrCreate(["name" => "gestionarMedios"]);
        $role = Role::firstOrCreate(["name" => "admin"]);
        $role->permissions()->syncWithoutDetaching($permission->id);

        $this->user = User::factory()->create();
        $this->user->roles()->syncWithoutDetaching($role->id);

        $this->actingAs($this->user, 'sanctum');
    }

    /** @test */
    public function a_user_can_upload_a_file()
    {
        $file = UploadedFile::fake()->create("avatar.jpg", 100, "image/jpeg"); // Use create instead of image

        $response = $this->postJson("/api/media", [
            "file" => $file,
            "alt_text" => "User avatar",
        ]);

        $response->assertStatus(201)->assertJson([
            "data" => [
                "file_name" => "avatar.jpg",
                "mime_type" => "image/jpeg",
                "size_bytes" => $file->getSize(),
                "alt_text" => "User avatar",
                "uploaded_by" => $this->user->id,
            ],
        ]);

        Storage::disk("public")->assertExists("media/" . $file->hashName());
        $this->assertDatabaseHas("media", [
            "file_name" => "avatar.jpg",
            "mime_type" => "image/jpeg",
            "size_bytes" => $file->getSize(),
            "alt_text" => "User avatar",
            "uploaded_by" => $this->user->id,
        ]);
    }

    /** @test */
    public function a_file_is_required_for_upload()
    {
        $response = $this->postJson("/api/media", [
            "alt_text" => "Some text",
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(["file"]);
    }

    /** @test */
    public function a_user_can_retrieve_all_media()
    {
        Media::factory()
            ->count(3)
            ->create(["uploaded_by" => $this->user->id]);

        $response = $this->getJson("/api/media");

        $response->assertStatus(200)->assertJsonCount(3, "data");
    }

    /** @test */
    public function a_user_can_retrieve_a_single_media_item()
    {
        $media = Media::factory()->create(["uploaded_by" => $this->user->id]);

        $response = $this->getJson("/api/media/" . $media->id);

        $response->assertStatus(200)->assertJson(["data" => $media->toArray()]);
    }

    /** @test */
    public function a_user_can_update_media_item()
    {
        $media = Media::factory()->create([
            "uploaded_by" => $this->user->id,
            "alt_text" => "Old text",
        ]);
        $updatedData = ["alt_text" => "New alternative text"];

        $response = $this->putJson("/api/media/" . $media->id, $updatedData);

        $response->assertStatus(200)->assertJson(["data" => $updatedData]);

        $this->assertDatabaseHas("media", $updatedData);
    }

    /** @test */
    public function a_user_can_delete_a_media_item()
    {
        Storage::disk("public")->put("media/test_file.jpg", "dummy content");
        $media = Media::factory()->create([
            "uploaded_by" => $this->user->id,
            "url" => Storage::disk("public")->url("media/test_file.jpg"),
            "file_name" => "test_file.jpg",
            "mime_type" => "image/jpeg",
            "size_bytes" => 1234,
        ]);

        $response = $this->deleteJson("/api/media/" . $media->id);

        $response->assertStatus(204);

        Storage::disk("public")->assertMissing("media/test_file.jpg");
        $this->assertDatabaseMissing("media", ["id" => $media->id]);
    }
}
