<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class FormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $permission = Permission::firstOrCreate(["name" => "gestionarFormularios"]);
        $role = Role::firstOrCreate(["name" => "admin"]);
        $role->permissions()->syncWithoutDetaching($permission->id);

        $user = User::factory()->create();
        $user->roles()->syncWithoutDetaching($role->id);

        $this->actingAs($user, 'sanctum');
    }

    /** @test */
    public function a_user_can_retrieve_all_forms()
    {
        Form::factory()->count(3)->create();

        $response = $this->getJson("/api/forms");

        $response->assertStatus(200)->assertJsonCount(3, "data");
    }

    /** @test */
    public function a_user_can_create_a_form()
    {
        $formData = [
            "name" => "Contact Us Form",
            "code" => "contact_us",
            "schema_json" => json_encode([
                "fields" => [
                    ["name" => "name", "type" => "string", "required" => true],
                    ["name" => "email", "type" => "email", "required" => true],
                    [
                        "name" => "message",
                        "type" => "string",
                        "required" => true,
                    ],
                ],
            ]),
            "is_active" => true,
        ];

        $response = $this->postJson("/api/forms", $formData);

        $response->assertStatus(201)->assertJson([
            "data" => [
                "name" => "Contact Us Form",
                "code" => "contact_us",
            ],
        ]);

        $this->assertDatabaseHas("forms", [
            "name" => "Contact Us Form",
            "code" => "contact_us",
            "created_by" => Auth::id(),
        ]);
    }

    /** @test */
    public function a_form_name_and_code_and_schema_are_required()
    {
        $response = $this->postJson("/api/forms", [
            "name" => null,
            "code" => null,
            "schema_json" => null,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(["name", "code", "schema_json"]);
    }

    /** @test */
    public function a_form_code_must_be_unique()
    {
        Form::factory()->create(["code" => "existing_code"]);

        $formData = [
            "name" => "Another Form",
            "code" => "existing_code",
            "schema_json" => json_encode(["fields" => []]),
        ];

        $response = $this->postJson("/api/forms", $formData);

        $response->assertStatus(422)->assertJsonValidationErrors(["code"]);
    }

    /** @test */
    public function a_user_can_retrieve_a_single_form()
    {
        $form = Form::factory()->create();
        $form->load("creator");

        $response = $this->getJson("/api/forms/" . $form->id);

        $expectedData = $form->toArray();
        $expectedData["creator"] = [
            "id" => $form->creator->id,
            "name" => $form->creator->name,
            "email" => $form->creator->email,
            "email_verified_at" => $form->creator->email_verified_at
                ? $form->creator->email_verified_at->toISOString()
                : null,
            "created_at" => $form->creator->created_at->toISOString(),
            "updated_at" => $form->creator->updated_at->toISOString(),
        ];

        $response->assertStatus(200)->assertJson(["data" => $expectedData]);
    }

    /** @test */
    public function a_user_can_update_a_form()
    {
        $form = Form::factory()->create([
            "name" => "Old Form",
            "code" => "old_form",
            "is_active" => false,
        ]);

        $updatedData = [
            "name" => "Updated Form",
            "code" => "updated_form",
            "schema_json" => json_encode([
                "fields" => [["name" => "new_field", "type" => "string"]],
            ]),
            "is_active" => true,
        ];

        $response = $this->putJson("/api/forms/" . $form->id, $updatedData);

        $response->assertStatus(200)->assertJson([
            "data" => [
                "name" => "Updated Form",
                "code" => "updated_form",
            ],
        ]);

        $this->assertDatabaseHas("forms", [
            "id" => $form->id,
            "name" => "Updated Form",
            "code" => "updated_form",
            "is_active" => true,
        ]);
    }

    /** @test */
    public function a_user_can_delete_a_form()
    {
        $form = Form::factory()->create();

        $response = $this->deleteJson("/api/forms/" . $form->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing("forms", ["id" => $form->id]);
    }
}
