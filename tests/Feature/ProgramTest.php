<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\User;
use App\Models\Faculty;
use App\Models\Person;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ProgramTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $permission = Permission::firstOrCreate(["name" => "gestionarProgramas"]);
        $role = Role::firstOrCreate(["name" => "admin"]);
        $role->permissions()->syncWithoutDetaching($permission->id);

        $user = User::factory()->create();
        $user->roles()->syncWithoutDetaching($role->id);

        $this->actingAs($user, 'sanctum');
    }

    /** @test */
    public function a_user_can_retrieve_all_programs()
    {
        Program::factory()->count(3)->create();

        $response = $this->getJson("/api/programs");

        $response->assertStatus(200)->assertJsonCount(3, 'data');
    }

    /** @test */
    public function a_user_can_create_a_program()
    {
        $faculty = Faculty::factory()->create();
        $people = Person::factory()->count(2)->create();

        $programData = [
            "faculty_id" => $faculty->id,
            "name" => "Computer Science",
            "degree_type" => "Bachelor",
            "duration_semesters" => 8,
            "modality" => "Presencial",
            "description" => "A program for computer science.",
            "person_ids" => $people->pluck("id")->toArray(),
        ];

        $response = $this->postJson("/api/programs", $programData);

        $response
            ->assertStatus(201)
            ->assertJson(['data' => ["name" => "Computer Science"]])
            ->assertJsonCount(2, "data.people");

        $this->assertDatabaseHas("programs", [
            "name" => "Computer Science",
            "slug" => Str::slug("Computer Science"),
            "faculty_id" => $faculty->id,
        ]);

        $program = Program::firstWhere("name", "Computer Science");
        $this->assertCount(2, $program->people);
    }

    /** @test */
    public function a_program_name_is_required()
    {
        $response = $this->postJson("/api/programs", ["name" => null]);

        $response->assertStatus(422)->assertJsonValidationErrors(["name"]);
    }

    /** @test */
    public function a_program_slug_must_be_unique()
    {
        Program::factory()->create(["slug" => "existing-program-slug"]);

        $programData = [
            "name" => "Another Program",
            "slug" => "existing-program-slug",
        ];

        $response = $this->postJson("/api/programs", $programData);

        $response->assertStatus(422)->assertJsonValidationErrors(["slug"]);
    }

    /** @test */
    public function a_user_can_retrieve_a_single_program()
    {
        $program = Program::factory()->create();

        $response = $this->getJson("/api/programs/" . $program->id);

        $response->assertStatus(200)->assertJson(['data' => $program->toArray()]);
    }

    /** @test */
    public function a_user_can_update_a_program()
    {
        $program = Program::factory()->create([
            "name" => "Old Program Name",
            "slug" => "old-program-name",
        ]);
        $newFaculty = Faculty::factory()->create();
        $newPeople = Person::factory()->count(1)->create();

        $updatedData = [
            "faculty_id" => $newFaculty->id,
            "name" => "Updated Program Name",
            "slug" => "updated-program-name",
            "degree_type" => "Master",
            "duration_semesters" => 4,
            "modality" => "Virtual",
            "description" => "Updated program description.",
            "person_ids" => $newPeople->pluck("id")->toArray(),
        ];

        $response = $this->putJson(
            "/api/programs/" . $program->id,
            $updatedData,
        );

        $response
            ->assertStatus(200)
            ->assertJson(['data' => ["name" => "Updated Program Name"]])
            ->assertJsonCount(1, "data.people");

        $this->assertDatabaseHas("programs", [
            "id" => $program->id,
            "name" => "Updated Program Name",
            "slug" => "updated-program-name",
            "faculty_id" => $newFaculty->id,
        ]);

        $program->refresh();
        $this->assertCount(1, $program->people);
    }

    /** @test */
    public function a_user_can_delete_a_program()
    {
        $program = Program::factory()->create();

        $response = $this->deleteJson("/api/programs/" . $program->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing("programs", ["id" => $program->id]);
    }
}
