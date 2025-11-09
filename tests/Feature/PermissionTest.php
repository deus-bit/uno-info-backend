<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $permissionGestionarPermisos = Permission::factory()->create(["name" => "gestionarPermisos"]);
        $permissionGestionarUsuarios = Permission::factory()->create(["name" => "gestionarUsuarios"]);
        $permissionGestionarRoles = Permission::factory()->create(["name" => "gestionarRoles"]);

        $role = Role::factory()->create(["name" => "admin"]);
        $role->permissions()->syncWithoutDetaching([
            $permissionGestionarPermisos->id,
            $permissionGestionarUsuarios->id,
            $permissionGestionarRoles->id,
        ]);

        $this->user = User::factory()->create();
        $this->user->roles()->syncWithoutDetaching($role->id);

        $this->actingAs($this->user, 'sanctum');
    }

    /** @test */
    public function a_user_can_retrieve_all_permissions()
    {
        $permissions = Permission::factory()->count(3)->create();

        $response = $this->getJson("/api/permissions");

        $response
            ->assertStatus(200)
            ->assertJsonCount(6, 'data');

        foreach ($permissions as $permission) {
            $response->assertJsonFragment($permission->toArray());
        }
    }

    /** @test */
    public function a_user_can_create_a_permission()
    {
        $permissionData = ["name" => "create-post"];

        $response = $this->postJson("/api/permissions", $permissionData);

        $response->assertStatus(201)->assertJson(['data' => $permissionData]);

        $this->assertDatabaseHas("permissions", $permissionData);
    }

    /** @test */
    public function a_permission_name_must_be_unique()
    {
        Permission::factory()->create(["name" => "existing-permission"]);

        $permissionData = ["name" => "existing-permission"];

        $response = $this->postJson("/api/permissions", $permissionData);

        $response->assertStatus(422)->assertJsonValidationErrors(["name"]);
    }

    /** @test */
    public function a_user_can_retrieve_a_single_permission()
    {
        $permission = Permission::factory()->create();

        $response = $this->getJson("/api/permissions/" . $permission->id);

        $response->assertStatus(200)->assertJson(['data' => $permission->toArray()]);
    }

    /** @test */
    public function a_user_can_update_a_permission()
    {
        $permission = Permission::factory()->create(["name" => "old-name"]);
        $updatedData = ["name" => "new-name"];

        $response = $this->putJson(
            "/api/permissions/" . $permission->id,
            $updatedData,
        );

        $response->assertStatus(200)->assertJson(['data' => $updatedData]);

        $this->assertDatabaseHas("permissions", $updatedData);
    }

    /** @test */
    public function a_user_can_delete_a_permission()
    {
        $permission = Permission::factory()->create();

        $response = $this->deleteJson("/api/permissions/" . $permission->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing("permissions", ["id" => $permission->id]);
    }

    /** @test */
    public function a_user_can_retrieve_permissions_for_a_user()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $permissions = Permission::factory()->count(2)->create();

        $role->permissions()->attach($permissions->pluck('id'));
        $user->roles()->attach($role->id);

        $response = $this->getJson('/api/users/' . $user->id . '/permissions');

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJson($permissions->toArray());
    }

    /** @test */
    public function a_user_can_retrieve_permissions_for_a_role()
    {
        $role = Role::factory()->create();
        $permissions = Permission::factory()->count(2)->create();
        $role->permissions()->attach($permissions->pluck('id'));

        $response = $this->getJson('/api/roles/' . $role->id . '/permissions');

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJson($permissions->toArray());
    }
}
