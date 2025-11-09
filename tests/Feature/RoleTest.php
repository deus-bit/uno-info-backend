<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $permission = Permission::factory()->create(["name" => "gestionarRoles"]);
        $role = Role::factory()->create(["name" => "admin"]);
        $role->permissions()->syncWithoutDetaching($permission->id);

        $this->user = User::factory()->create();
        $this->user->roles()->syncWithoutDetaching($role->id);

        $this->actingAs($this->user, 'sanctum');
    }

    /** @test */
    public function a_user_can_retrieve_all_roles()
    {
        $roles = Role::factory()->count(3)->create();

        $response = $this->getJson("/api/roles");

        $response
            ->assertStatus(200)
            ->assertJsonCount(4, "data");

        foreach ($roles as $role) {
            $response->assertJsonFragment($role->toArray());
        }
    }

    /** @test */
    public function a_user_can_create_a_role()
    {
        $roleData = ["name" => "new-role"];

        $response = $this->postJson("/api/roles", $roleData);

        $response->assertStatus(201)->assertJson(["data" => $roleData]);

        $this->assertDatabaseHas("roles", $roleData);
    }

    /** @test */
    public function a_role_name_must_be_unique()
    {
        Role::factory()->create(["name" => "existing-role"]);

        $roleData = ["name" => "existing-role"];

        $response = $this->postJson("/api/roles", $roleData);

        $response->assertStatus(422)->assertJsonValidationErrors(["name"]);
    }

    /** @test */
    public function a_user_can_retrieve_a_single_role()
    {
        $role = Role::factory()->create();

        $response = $this->getJson("/api/roles/" . $role->id);

        $response->assertStatus(200)->assertJson(["data" => $role->toArray()]);
    }

    /** @test */
    public function a_user_can_update_a_role()
    {
        $role = Role::factory()->create(["name" => "old-name"]);
        $updatedData = ["name" => "new-name"];

        $response = $this->putJson("/api/roles/" . $role->id, $updatedData);

        $response->assertStatus(200)->assertJson(["data" => $updatedData]);

        $this->assertDatabaseHas("roles", $updatedData);
    }

    /** @test */
    public function a_user_can_delete_a_role()
    {
        $role = Role::factory()->create();

        $response = $this->deleteJson("/api/roles/" . $role->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing("roles", ["id" => $role->id]);
    }
}