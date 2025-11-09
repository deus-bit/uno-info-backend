<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $permission = Permission::firstOrCreate(["name" => "gestionarCategorias"]);
        $role = Role::firstOrCreate(["name" => "admin"]);
        $role->permissions()->syncWithoutDetaching($permission->id);

        $user = User::factory()->create();
        $user->roles()->syncWithoutDetaching($role->id);

        $this->actingAs($user, 'sanctum');
    }

    /** @test */
    public function a_user_can_retrieve_all_categories()
    {
        $categories = Category::factory()->count(3)->create();

        $response = $this->getJson("/api/categories");

        $response
            ->assertStatus(200)
            ->assertJsonCount(3, "data")
            ->assertJson(["data" => $categories->toArray()]);
    }

    /** @test */
    public function a_user_can_create_a_category()
    {
        $categoryData = ["name" => "New Category"];

        $response = $this->postJson("/api/categories", $categoryData);

        $response
            ->assertStatus(201)
            ->assertJson([
                "data" => [
                    "name" => "New Category",
                    "slug" => Str::slug("New Category"),
                ],
            ]);

        $this->assertDatabaseHas("categories", [
            "name" => "New Category",
            "slug" => Str::slug("New Category"),
        ]);
    }

    /** @test */
    public function a_category_name_is_required()
    {
        $response = $this->postJson("/api/categories", ["name" => null]);

        $response->assertStatus(422)->assertJsonValidationErrors(["name"]);
    }

    /** @test */
    public function a_category_slug_must_be_unique()
    {
        Category::factory()->create(["slug" => "existing-slug"]);

        $categoryData = [
            "name" => "Another Category",
            "slug" => "existing-slug",
        ];

        $response = $this->postJson("/api/categories", $categoryData);

        $response->assertStatus(422)->assertJsonValidationErrors(["slug"]);
    }

    /** @test */
    public function a_user_can_retrieve_a_single_category()
    {
        $category = Category::factory()->create();

        $response = $this->getJson("/api/categories/" . $category->id);

        $response
            ->assertStatus(200)
            ->assertJson(["data" => $category->toArray()]);
    }

    /** @test */
    public function a_user_can_update_a_category()
    {
        $category = Category::factory()->create([
            "name" => "Old Name",
            "slug" => "old-name",
        ]);
        $updatedData = ["name" => "Updated Name", "slug" => "updated-name"];

        $response = $this->putJson(
            "/api/categories/" . $category->id,
            $updatedData,
        );

        $response->assertStatus(200)->assertJson(["data" => $updatedData]);

        $this->assertDatabaseHas("categories", $updatedData);
    }

    /** @test */
    public function a_user_can_delete_a_category()
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/categories/" . $category->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing("categories", ["id" => $category->id]);
    }
}
