<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $permission = Permission::factory()->create(["name" => "gestionarUsuarios"]);
        $role = Role::factory()->create(["name" => "admin"]);
        $role->permissions()->syncWithoutDetaching($permission->id);

        $this->user = User::factory()->create();
        $this->user->roles()->syncWithoutDetaching($role->id);

        $this->actingAs($this->user, 'sanctum');
    }

    /** @test */
    public function a_user_can_retrieve_all_users()
    {
        $users = User::factory()->count(3)->create();

        $response = $this->getJson("/api/users");

        $response->assertStatus(200)->assertJsonCount(4, "data");
    }

    /** @test */
    public function a_user_can_create_a_user()
    {
        $userData = [
            "name" => "Test User",
            "email" => "test@example.com",
            "password" => "password",
        ];

        $response = $this->postJson("/api/users", $userData);

        $response
            ->assertStatus(201)
            ->assertJson([
                "data" => [
                    "name" => "Test User",
                    "email" => "test@example.com",
                ],
            ]);

        $this->assertDatabaseHas("users", [
            "name" => "Test User",
            "email" => "test@example.com",
        ]);
        $this->assertTrue(Hash::check("password", User::first()->password));
    }

    /** @test */
    public function a_user_email_must_be_unique()
    {
        User::factory()->create(["email" => "existing@example.com"]);

        $userData = [
            "name" => "Another User",
            "email" => "existing@example.com",
            "password" => "password",
        ];

        $response = $this->postJson("/api/users", $userData);

        $response->assertStatus(422)->assertJsonValidationErrors(["email"]);
    }

    /** @test */
    public function a_user_can_retrieve_a_single_user()
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/users/" . $user->id);

        $response->assertStatus(200)->assertJson([
            "data" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
            ],
        ]);
    }

    /** @test */
    public function a_user_can_update_a_user()
    {
        $user = User::factory()->create([
            "name" => "Old Name",
            "email" => "old@example.com",
        ]);
        $updatedData = [
            "name" => "New Name",
            "email" => "new@example.com",
            "password" => "new_password",
        ];

        $response = $this->putJson("/api/users/" . $user->id, $updatedData);

        $response
            ->assertStatus(200)
            ->assertJson([
                "data" => ["name" => "New Name", "email" => "new@example.com"],
            ]);

        $this->assertDatabaseHas("users", [
            "name" => "New Name",
            "email" => "new@example.com",
        ]);
        $this->assertTrue(
            Hash::check("new_password", $user->fresh()->password),
        );
    }

    /** @test */
    public function a_user_can_delete_a_user()
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/users/" . $user->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing("users", ["id" => $user->id]);
    }
}
