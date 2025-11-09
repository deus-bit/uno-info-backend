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

        $permission = Permission::factory()->create([
            "name" => "gestionarUsuarios",
        ]);
        $role = Role::factory()->create(["name" => "admin"]);
        $role->permissions()->syncWithoutDetaching($permission->id);

        $this->user = User::factory()->create();
        $this->user->roles()->syncWithoutDetaching($role->id);

        $this->actingAs($this->user, "sanctum");
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
        $role = Role::factory()->create(["name" => "test-role"]);
        $userData = [
            "name" => "Test User",
            "email" => "test@example.com",
            "password" => "password",
            "role_ids" => [$role->id],
        ];

        $response = $this->postJson("/api/users", $userData);

        $response->assertStatus(201)->assertJson([
            "data" => [
                "name" => "Test User",
                "email" => "test@example.com",
                "roles" => [["id" => $role->id, "name" => "test-role"]],
            ],
        ]);

        $this->assertDatabaseHas("users", [
            "name" => "Test User",
            "email" => "test@example.com",
        ]);
        $createdUser = User::where("email", $userData["email"])->first();
        $this->assertTrue(Hash::check("password", $createdUser->password));
        $this->assertTrue($createdUser->fresh()->roles->contains($role));
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
        $role = Role::factory()->create(["name" => "updated-role"]);
        $updatedData = [
            "name" => "New Name",
            "email" => "new@example.com",
            "password" => "new_password",
            "role_ids" => [$role->id],
        ];

        $response = $this->patchJson("/api/users/" . $user->id, $updatedData);

        $response->assertStatus(200)->assertJson([
            "data" => [
                "name" => "New Name",
                "email" => "new@example.com",
                "roles" => [["id" => $role->id, "name" => "updated-role"]],
            ],
        ]);

        $this->assertDatabaseHas("users", [
            "name" => "New Name",
            "email" => "new@example.com",
        ]);
        $this->assertTrue(
            Hash::check("new_password", $user->fresh()->password),
        );
        $this->assertTrue($user->fresh()->roles->contains($role));
    }

    /** @test */
    public function a_user_can_delete_a_user()
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/users/" . $user->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing("users", ["id" => $user->id]);
    }

    /** @test */
    public function a_user_can_partially_update_a_user()
    {
        $user = User::factory()->create([
            "name" => "Original Name",
            "email" => "original@example.com",
            "password" => Hash::make("original_password"),
        ]);
        $originalPassword = $user->password;

        $role1 = Role::factory()->create(["name" => "role-one"]);
        $role2 = Role::factory()->create(["name" => "role-two"]);
        $user->roles()->attach($role1);

        $updatedData = [
            "name" => "New Partial Name",
            "role_ids" => [$role2->id],
        ];

        $response = $this->patchJson("/api/users/" . $user->id, $updatedData);

        $response->assertStatus(200)->assertJson([
            "data" => [
                "name" => "New Partial Name",
                "email" => "original@example.com", // Email should remain unchanged
                "roles" => [["id" => $role2->id, "name" => "role-two"]],
            ],
        ]);

        $this->assertDatabaseHas("users", [
            "id" => $user->id,
            "name" => "New Partial Name",
            "email" => "original@example.com",
        ]);
        $this->assertEquals($originalPassword, $user->fresh()->password); // Password should remain unchanged
        $this->assertFalse($user->fresh()->roles->contains($role1)); // Old role should be detached
        $this->assertTrue($user->fresh()->roles->contains($role2)); // New role should be attached
    }
}
