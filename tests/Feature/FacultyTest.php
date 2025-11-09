<?php

namespace Tests\Feature;

use App\Models\Faculty;
use App\Models\User;
use App\Models\Person;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class FacultyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $permission = Permission::firstOrCreate(["name" => "gestionarFacultades"]);
        $role = Role::firstOrCreate(["name" => "admin"]);
        $role->permissions()->syncWithoutDetaching($permission->id);

        $user = User::factory()->create();
        $user->roles()->syncWithoutDetaching($role->id);

        $this->actingAs($user, 'sanctum');
    }

    /** @test */
    public function a_user_can_retrieve_all_faculties()
    {
        Faculty::factory()->count(3)->create();

        $response = $this->getJson("/api/faculties");

        $response->assertStatus(200)->assertJsonCount(3, "data");
    }

    /** @test */
    public function a_user_can_create_a_faculty()
    {
        $dean = Person::factory()->create();

        $facultyData = [
            "name" => "Faculty of Engineering",
            "description" => "Description for engineering faculty.",
            "dean_person_id" => $dean->id,
        ];

        $response = $this->postJson("/api/faculties", $facultyData);

        $response->assertStatus(201)->assertJson([
            "data" => [
                "name" => "Faculty of Engineering",
                "dean_person_id" => $dean->id,
            ],
        ]);

        $this->assertDatabaseHas("faculties", [
            "name" => "Faculty of Engineering",
            "slug" => Str::slug("Faculty of Engineering"),
            "dean_person_id" => $dean->id,
        ]);
    }

    /** @test */
    public function a_faculty_name_is_required()
    {
        $response = $this->postJson("/api/faculties", ["name" => null]);

        $response->assertStatus(422)->assertJsonValidationErrors(["name"]);
    }

    /** @test */
    public function a_faculty_slug_must_be_unique()
    {
        Faculty::factory()->create(["slug" => "existing-faculty-slug"]);

        $facultyData = [
            "name" => "Another Faculty",
            "slug" => "existing-faculty-slug",
        ];

        $response = $this->postJson("/api/faculties", $facultyData);

        $response->assertStatus(422)->assertJsonValidationErrors(["slug"]);
    }

    /** @test */
    public function a_user_can_retrieve_a_single_faculty()
    {
        $faculty = Faculty::factory()->create();

        $response = $this->getJson("/api/faculties/" . $faculty->id);

        $response
            ->assertStatus(200)
            ->assertJson(["data" => $faculty->toArray()]);
    }

    /** @test */
    public function a_user_can_update_a_faculty()
    {
        $faculty = Faculty::factory()->create([
            "name" => "Old Faculty Name",
            "slug" => "old-faculty-name",
        ]);
        $newDean = Person::factory()->create();

        $updatedData = [
            "name" => "Updated Faculty Name",
            "slug" => "updated-faculty-name",
            "description" => "Updated description.",
            "dean_person_id" => $newDean->id,
        ];

        $response = $this->putJson(
            "/api/faculties/" . $faculty->id,
            $updatedData,
        );

        $response->assertStatus(200)->assertJson([
            "data" => [
                "name" => "Updated Faculty Name",
                "dean_person_id" => $newDean->id,
            ],
        ]);

        $this->assertDatabaseHas("faculties", [
            "id" => $faculty->id,
            "name" => "Updated Faculty Name",
            "slug" => "updated-faculty-name",
            "dean_person_id" => $newDean->id,
        ]);
    }

    /** @test */
    public function a_user_can_delete_a_faculty()
    {
        $faculty = Faculty::factory()->create();

        $response = $this->deleteJson("/api/faculties/" . $faculty->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing("faculties", ["id" => $faculty->id]);
    }
}
