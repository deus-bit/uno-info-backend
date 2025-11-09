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

        $permission = Permission::factory()->create([
            "name" => "gestionarRoles",
        ]);
        $role = Role::factory()->create(["name" => "admin"]);
        $role->permissions()->syncWithoutDetaching($permission->id);

        $this->user = User::factory()->create();
        $this->user->roles()->syncWithoutDetaching($role->id);

        $this->actingAs($this->user, "sanctum");
    }

    /** @test */
    public function a_user_can_retrieve_all_roles()
    {
        $permission1 = Permission::factory()->create([
            "name" => "permission-a",
        ]);
        $permission2 = Permission::factory()->create([
            "name" => "permission-b",
        ]);

        $role1 = Role::factory()->create(['description' => 'Description for Role 1']);
        $role1->permissions()->attach($permission1);

        $role2 = Role::factory()->create(['description' => 'Description for Role 2']);
        $role2->permissions()->attach($permission2);

        $role3 = Role::factory()->create(['description' => 'Description for Role 3']); // Role without explicit permissions

        $response = $this->getJson("/api/roles");

        $response->assertStatus(200)->assertJsonCount(4, "data"); // 1 admin role + 3 created roles

        $response->assertJsonFragment([
            "id" => $role1->id,
            "name" => $role1->name,
            "description" => "Description for Role 1",
            "permissions" => [
                ["id" => $permission1->id, "name" => "permission-a"],
            ],
            "created_at" => $role1->created_at->toISOString(),
            "updated_at" => $role1->updated_at->toISOString(),
        ]);

        $response->assertJsonFragment([
            "id" => $role2->id,
            "name" => $role2->name,
            "description" => "Description for Role 2",
            "permissions" => [
                ["id" => $permission2->id, "name" => "permission-b"],
            ],
            "created_at" => $role2->created_at->toISOString(),
            "updated_at" => $role2->updated_at->toISOString(),
        ]);

        $response->assertJsonFragment([
            "id" => $role3->id,
            "name" => $role3->name,
            "description" => "Description for Role 3",
            "permissions" => [], // No permissions attached
            "created_at" => $role3->created_at->toISOString(),
            "updated_at" => $role3->updated_at->toISOString(),
        ]);
    }

    /** @test */
    public function a_user_can_create_a_role()
    {
        $permission = Permission::factory()->create([
            "name" => "test-permission",
        ]);
        $roleData = [
            "name" => "new-role",
            "description" => "Description for new role",
            "permission_ids" => [$permission->id],
        ];

        $response = $this->postJson("/api/roles", $roleData);

        $response->assertStatus(201)->assertJson([
            "data" => [
                "name" => "new-role",
                "description" => "Description for new role",
                "permissions" => [
                    ["id" => $permission->id, "name" => "test-permission"],
                ],
            ],
        ]);

        $this->assertDatabaseHas("roles", ["name" => "new-role"]);
        $createdRole = Role::where("name", "new-role")->first();
        $this->assertTrue(
            $createdRole->fresh()->permissions->contains($permission),
        );
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
        $role = Role::factory()->create(['description' => 'Single role description']);
        $permission = Permission::factory()->create(["name" => "view-role"]);
        $role->permissions()->attach($permission);

        $response = $this->getJson("/api/roles/" . $role->id);

        $response->assertStatus(200)->assertJson([
            "data" => [
                "id" => $role->id,
                "name" => $role->name,
                "description" => "Single role description",
                "permissions" => [
                    ["id" => $permission->id, "name" => "view-role"],
                ],
                "created_at" => $role->created_at->toISOString(),
                "updated_at" => $role->updated_at->toISOString(),
            ],
        ]);
    }

    /** @test */
    public function a_user_can_update_a_role()
    {
        $role = Role::factory()->create(["name" => "old-name", "description" => "Old description"]);
        $permission = Permission::factory()->create([
            "name" => "updated-permission",
        ]);
        $updatedData = [
            "name" => "new-name",
            "description" => "New description",
            "permission_ids" => [$permission->id],
        ];

        $response = $this->patchJson("/api/roles/" . $role->id, $updatedData);

        $response->assertStatus(200)->assertJson([
            "data" => [
                "name" => "new-name",
                "description" => "New description",
                "permissions" => [
                    ["id" => $permission->id, "name" => "updated-permission"],
                ],
            ],
        ]);

        $this->assertDatabaseHas("roles", ["name" => "new-name", "description" => "New description"]);
        $this->assertTrue($role->fresh()->permissions->contains($permission));
    }

    /** @test */
    public function a_user_can_delete_a_role()
    {
        $role = Role::factory()->create();

        $response = $this->deleteJson("/api/roles/" . $role->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing("roles", ["id" => $role->id]);
    }

        /** @test */

        public function a_user_can_partially_update_a_role()

        {

            $role = Role::factory()->create(["name" => "Original Role Name", "description" => "Original description"]);

            $permission1 = Permission::factory()->create([

                "name" => "permission-one",

            ]);

            $permission2 = Permission::factory()->create([

                "name" => "permission-two",

            ]);

            $role->permissions()->attach($permission1);

    

            $updatedData = [

                "name" => "New Partial Role Name",

                "permission_ids" => [$permission2->id],

            ];

    

            $response = $this->patchJson("/api/roles/" . $role->id, $updatedData);

    

            $response

                ->assertStatus(200)

                ->assertJson([

                    "data" => [

                        "name" => "New Partial Role Name",

                        "description" => "Original description", // Description should remain unchanged

                        "permissions" => [

                            ["id" => $permission2->id, "name" => "permission-two"],

                        ],

                    ],

                ]);

    

            $this->assertDatabaseHas("roles", [

                "id" => $role->id,

                "name" => "New Partial Role Name",

                "description" => "Original description",

            ]);

            $this->assertFalse($role->fresh()->permissions->contains($permission1)); // Old permission should be detached

            $this->assertTrue($role->fresh()->permissions->contains($permission2)); // New permission should be attached

        }
}
