<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
use App\Models\Media;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class FormSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $permission = Permission::firstOrCreate(["name" => "gestionarEnviosFormulario"]);
        $role = Role::firstOrCreate(["name" => "admin"]);
        $role->permissions()->syncWithoutDetaching($permission->id);

        $user = User::factory()->create();
        $user->roles()->syncWithoutDetaching($role->id);

        $this->actingAs($user, 'sanctum');
    }

    /** @test */
    public function a_user_can_retrieve_all_form_submissions()
    {
        FormSubmission::factory()->count(3)->create();

        $response = $this->getJson("/api/form-submissions");

        $response->assertStatus(200)->assertJsonCount(3, "data");
    }

    /** @test */
    public function a_user_can_create_a_form_submission()
    {
        $form = Form::factory()->create([
            "is_active" => true,
            "schema_json" => json_encode([
                "fields" => [
                    ["name" => "name", "type" => "string", "required" => true],
                    ["name" => "email", "type" => "email", "required" => true],
                    [
                        "name" => "message",
                        "type" => "string",
                        "required" => false,
                    ],
                ],
            ]),
        ]);
        $media = Media::factory()->create();

        $submissionData = [
            "form_id" => $form->id,
            "payload_json" => json_encode([
                "name" => "Test User",
                "email" => "test@example.com",
                "message" => "Hello World",
            ]),
            "attachment_media_id" => $media->id,
        ];

        $response = $this->postJson("/api/form-submissions", $submissionData);

        $response->assertStatus(201)->assertJson([
            "data" => [
                "form_id" => $form->id,
                "attachment_media_id" => $media->id,
            ],
        ]);

        $this->assertDatabaseHas("form_submissions", [
            "form_id" => $form->id,
            "attachment_media_id" => $media->id,
        ]);
    }

    /** @test */
    public function a_form_submission_requires_a_valid_form_id_and_payload()
    {
        $response = $this->postJson("/api/form-submissions", [
            "form_id" => 999, // Non-existent form
            "payload_json" => null,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(["form_id", "payload_json"]);
    }

    /** @test */
    public function a_form_submission_validates_against_form_schema()
    {
        $form = Form::factory()->create([
            "is_active" => true,
            "schema_json" => json_encode([
                "fields" => [
                    ["name" => "name", "type" => "string", "required" => true],
                    ["name" => "email", "type" => "email", "required" => true],
                ],
            ]),
        ]);

        // Missing required 'name' field
        $submissionData = [
            "form_id" => $form->id,
            "payload_json" => json_encode([
                "email" => "test@example.com",
            ]),
        ];

        $response = $this->postJson("/api/form-submissions", $submissionData);

        $response->assertStatus(422)->assertJsonValidationErrors(["name"]);
    }

    /** @test */
    public function a_form_submission_cannot_be_created_for_inactive_form()
    {
        $form = Form::factory()->create(["is_active" => false]);

        $submissionData = [
            "form_id" => $form->id,
            "payload_json" => json_encode(["name" => "Test"]),
        ];

        $response = $this->postJson("/api/form-submissions", $submissionData);

        $response->assertStatus(404)->assertJsonFragment([
            "message" => "Form not found or is inactive",
        ]);
    }

    /** @test */
    public function a_user_can_retrieve_a_single_form_submission()
    {
        $submission = FormSubmission::factory()->create();
        $submission->load("form", "attachmentMedia");

        $response = $this->getJson("/api/form-submissions/" . $submission->id);

        $expectedData = $submission->toArray();
        $expectedData["form"] = $submission->form->toArray();
        $expectedData[
            "attachment_media"
        ] = $submission->attachmentMedia->toArray();

        $response->assertStatus(200)->assertJson(["data" => $expectedData]);
    }

    /** @test */
    public function a_user_can_delete_a_form_submission()
    {
        $submission = FormSubmission::factory()->create();

        $response = $this->deleteJson(
            "/api/form-submissions/" . $submission->id,
        );

        $response->assertStatus(204);

        $this->assertDatabaseMissing("form_submissions", [
            "id" => $submission->id,
        ]);
    }
}
