<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;

class TagTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $permission = Permission::firstOrCreate(["name" => "gestionarEtiquetas"]);
        $role = Role::firstOrCreate(["name" => "admin"]);
        $role->permissions()->syncWithoutDetaching($permission->id);

        $user = User::factory()->create();
        $user->roles()->syncWithoutDetaching($role->id);

        $this->actingAs($user, 'sanctum');
    }

    /** @test */
    public function a_user_can_retrieve_all_tags()
    {
        $tags = Tag::factory()->count(3)->create();

        $response = $this->getJson("/api/tags");

        $response
            ->assertStatus(200)
            ->assertJsonCount(3, "data")
            ->assertJson(["data" => $tags->toArray()]);
    }

    /** @test */
    public function a_user_can_create_a_tag()
    {
        $tagData = ["name" => "New Tag"];

        $response = $this->postJson("/api/tags", $tagData);

        $response
            ->assertStatus(201)
            ->assertJson([
                "data" => ["name" => "New Tag", "slug" => Str::slug("New Tag")],
            ]);

        $this->assertDatabaseHas("tags", [
            "name" => "New Tag",
            "slug" => Str::slug("New Tag"),
        ]);
    }

    /** @test */
    public function a_tag_name_is_required()
    {
        $response = $this->postJson("/api/tags", ["name" => null]);

        $response->assertStatus(422)->assertJsonValidationErrors(["name"]);
    }

    /** @test */
    public function a_tag_slug_must_be_unique()
    {
        Tag::factory()->create(["slug" => "existing-slug"]);

        $tagData = ["name" => "Another Tag", "slug" => "existing-slug"];

        $response = $this->postJson("/api/tags", $tagData);

        $response->assertStatus(422)->assertJsonValidationErrors(["slug"]);
    }

    /** @test */
    public function a_user_can_retrieve_a_single_tag()
    {
        $tag = Tag::factory()->create();

        $response = $this->getJson("/api/tags/" . $tag->id);

        $response->assertStatus(200)->assertJson(["data" => $tag->toArray()]);
    }

    /** @test */
    public function a_user_can_update_a_tag()
    {
        $tag = Tag::factory()->create([
            "name" => "Old Name",
            "slug" => "old-name",
        ]);
        $updatedData = ["name" => "Updated Name", "slug" => "updated-name"];

        $response = $this->putJson("/api/tags/" . $tag->id, $updatedData);

        $response->assertStatus(200)->assertJson(["data" => $updatedData]);

        $this->assertDatabaseHas("tags", $updatedData);
    }

    /** @test */
    public function a_user_can_delete_a_tag()
    {
        $tag = Tag::factory()->create();

        $response = $this->deleteJson("/api/tags/" . $tag->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing("tags", ["id" => $tag->id]);
    }
}
