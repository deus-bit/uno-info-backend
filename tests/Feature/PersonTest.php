<?php

namespace Tests\Feature;

use App\Models\Person;
use App\Models\User;
use App\Models\Media;
use App\Models\Program;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class PersonTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $permission = Permission::factory()->create(["name" => "gestionarPersonas"]);
        $role = Role::factory()->create(["name" => "admin"]);
        $role->permissions()->syncWithoutDetaching($permission->id);

        $this->user = User::factory()->create();
        $this->user->roles()->syncWithoutDetaching($role->id);

        $this->actingAs($this->user, 'sanctum');
    }

    /** @test */
    public function a_user_can_retrieve_all_people()
    {
        Person::factory()->count(3)->create();

        $response = $this->getJson("/api/people");

        $response->assertStatus(200)->assertJsonCount(3, 'data');
    }

    /** @test */
    public function a_user_can_create_a_person()
    {
        $media = Media::factory()->create();
        $programs = Program::factory()->count(2)->create();

        $personData = [
            "full_name" => "John Doe",
            "position_title" => "Professor",
            "email" => "john.doe@example.com",
            "phone" => "123-456-7890",
            "biography" => "A brief biography.",
            "photo_media_id" => $media->id,
            "program_ids" => $programs->pluck("id")->toArray(),
        ];

        $response = $this->postJson("/api/people", $personData);

        $response
            ->assertStatus(201)
            ->assertJson(['data' => ["full_name" => "John Doe"]])
            ->assertJsonCount(2, "data.programs");

        $this->assertDatabaseHas("people", [
            "full_name" => "John Doe",
            "email" => "john.doe@example.com",
            "photo_media_id" => $media->id,
        ]);

        $person = Person::firstWhere("full_name", "John Doe");
        $this->assertCount(2, $person->programs);
    }

    /** @test */
    public function a_person_full_name_is_required()
    {
        $response = $this->postJson("/api/people", ["full_name" => null]);

        $response->assertStatus(422)->assertJsonValidationErrors(["full_name"]);
    }

    /** @test */
    public function a_user_can_retrieve_a_single_person()
    {
        $person = Person::factory()->has(Media::factory(), 'photoMedia')->has(Program::factory()->count(2), 'programs')->create();
        $person->load('photoMedia', 'programs');

        $response = $this->getJson("/api/people/" . $person->id);

        $expectedData = $person->toArray();
        $expectedData['photo_media'] = $person->photoMedia->toArray();
        $expectedData['programs'] = $person->programs->map(function ($program) {
            $programArray = $program->toArray();
            unset($programArray['pivot']);
            return $programArray;
        })->toArray();

        $response->assertStatus(200)->assertJson(['data' => $expectedData]);
    }

    /** @test */
    public function a_user_can_update_a_person()
    {
        $person = Person::factory()->create([
            "full_name" => "Old Name",
            "email" => "old@example.com",
        ]);
        $newMedia = Media::factory()->create();
        $newPrograms = Program::factory()->count(1)->create();

        $updatedData = [
            "full_name" => "New Name",
            "position_title" => "Associate Professor",
            "email" => "new@example.com",
            "photo_media_id" => $newMedia->id,
            "program_ids" => $newPrograms->pluck("id")->toArray(),
        ];

        $response = $this->putJson("/api/people/" . $person->id, $updatedData);

        $response
            ->assertStatus(200)
            ->assertJson(['data' => ["full_name" => "New Name"]])
            ->assertJsonCount(1, "data.programs");

        $this->assertDatabaseHas("people", [
            "id" => $person->id,
            "full_name" => "New Name",
            "email" => "new@example.com",
            "photo_media_id" => $newMedia->id,
        ]);

        $person->refresh();
        $this->assertCount(1, $person->programs);
    }

    /** @test */
    public function a_user_can_delete_a_person()
    {
        $person = Person::factory()->create();

        $response = $this->deleteJson("/api/people/" . $person->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing("people", ["id" => $person->id]);
    }
}
