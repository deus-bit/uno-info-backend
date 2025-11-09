<?php

namespace Tests\Feature;

use App\Models\Publication;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Media;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class PublicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $permission = Permission::firstOrCreate(["name" => "gestionarPublicaciones"]);
        $role = Role::firstOrCreate(["name" => "admin"]);
        $role->permissions()->syncWithoutDetaching($permission->id);

        $user = User::factory()->create();
        $user->roles()->syncWithoutDetaching($role->id);

        $this->actingAs($user, 'sanctum');
    }

    /** @test */
    public function a_user_can_retrieve_all_publications()
    {
        Publication::factory()->count(3)->create();

        $response = $this->getJson("/api/publications");

        $response->assertStatus(200)->assertJsonCount(3, 'data');
    }

    /** @test */
    public function a_user_can_create_a_publication()
    {
        $category = Category::factory()->create();
        $media = Media::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $publicationData = [
            "title" => "New Publication Title",
            "summary" => "A short summary.",
            "content_html" => "<p>Full content here.</p>",
            "category_id" => $category->id,
            "status" => "draft",
            "published_at" => now()->addDays(7)->format("Y-m-d H:i:s"),
            "cover_media_id" => $media->id,
            "featured" => true,
            "tags" => $tags->pluck("id")->toArray(),
        ];

        $response = $this->postJson("/api/publications", $publicationData);

        $response
            ->assertStatus(201)
            ->assertJsonFragment(["title" => "New Publication Title"])
            ->assertJsonCount(2, "data.tags"); // Assert that 2 tags are associated

        $this->assertDatabaseHas("publications", [
            "title" => "New Publication Title",
            "slug" => Str::slug("New Publication Title"),
            "category_id" => $category->id,
            "cover_media_id" => $media->id,
            "created_by" => Auth::id(),
            "updated_by" => Auth::id(),
            "featured" => true,
        ]);

        $publication = Publication::firstWhere(
            "title",
            "New Publication Title",
        );
        $this->assertCount(2, $publication->tags);
    }

    /** @test */
    public function a_publication_title_is_required()
    {
        $response = $this->postJson("/api/publications", ["title" => null]);

        $response->assertStatus(422)->assertJsonValidationErrors(["title"]);
    }

    /** @test */
    public function a_publication_slug_must_be_unique()
    {
        Publication::factory()->create(["slug" => "existing-slug"]);

        $publicationData = [
            "title" => "Another Publication",
            "slug" => "existing-slug",
            "status" => "draft",
        ];

        $response = $this->postJson("/api/publications", $publicationData);

        $response->assertStatus(422)->assertJsonValidationErrors(["slug"]);
    }

    /** @test */
    public function a_user_can_retrieve_a_single_publication()
    {
        $publication = Publication::factory()->has(Category::factory())->create();
        $publication->load('category');

        $response = $this->getJson("/api/publications/" . $publication->id);

        $expectedData = $publication->toArray();
        $expectedData['category_name'] = $publication->category->name;
        $expectedData['tags'] = [];

        $response->assertStatus(200)->assertJson(['data' => $expectedData]);
    }

    /** @test */
    public function a_user_can_update_a_publication()
    {
        $publication = Publication::factory()->create([
            "title" => "Old Title",
            "slug" => "old-title",
            "status" => "draft",
            "featured" => false,
        ]);
        $newCategory = Category::factory()->create();
        $newMedia = Media::factory()->create();
        $newTags = Tag::factory()->count(1)->create();

        $updatedData = [
            "title" => "Updated Title",
            "slug" => "updated-title",
            "summary" => "Updated summary.",
            "category_id" => $newCategory->id,
            "status" => "published",
            "published_at" => now()->format("Y-m-d H:i:s"),
            "cover_media_id" => $newMedia->id,
            "featured" => true,
            "tags" => $newTags->pluck("id")->toArray(),
        ];

        $response = $this->putJson(
            "/api/publications/" . $publication->id,
            $updatedData,
        );

        $response
            ->assertStatus(200)
            ->assertJsonFragment(["title" => "Updated Title"])
            ->assertJsonCount(1, "data.tags");

        $this->assertDatabaseHas("publications", [
            "id" => $publication->id,
            "title" => "Updated Title",
            "slug" => "updated-title",
            "category_id" => $newCategory->id,
            "cover_media_id" => $newMedia->id,
            "updated_by" => Auth::id(),
            "featured" => true,
        ]);

        $publication->refresh();
        $this->assertCount(1, $publication->tags);
    }

    /** @test */
    public function a_user_can_soft_delete_a_publication()
    {
        $publication = Publication::factory()->create();

        $response = $this->deleteJson("/api/publications/" . $publication->id);

        $response->assertStatus(204);

        $this->assertDatabaseHas("publications", [
            "id" => $publication->id,
            "soft_deleted" => true,
        ]);
    }
}
