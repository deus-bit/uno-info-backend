<?php

namespace App\Http\Controllers;

use App\Http\Resources\FormSubmissionResource;
use App\Http\Resources\FormResource;
use App\Http\Resources\MediaResource;
use App\Models\FormSubmission;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FormSubmissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $submissions = FormSubmission::with(
            "form",
            "attachmentMedia",
        )->paginate();
        return FormSubmissionResource::collection($submissions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "form_id" => ["required", "exists:forms,id"],
            "payload_json" => ["required", "json"],
            "attachment_media_id" => ["nullable", "exists:media,id"],
        ]);

        $form = Form::find($request->form_id);

        if (!$form || !$form->is_active) {
            return response()->json(
                ["message" => "Form not found or is inactive"],
                404,
            );
        }

        // Dynamic validation based on form schema_json
        $schema = json_decode($form->schema_json, true);
        $rules = $this->buildValidationRules($schema);
        $data = json_decode($request->payload_json, true);

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 422);
        }

        $submission = FormSubmission::create([
            "form_id" => $request->form_id,
            "submitted_at" => now(),
            "ip_address" => $request->ip(),
            "payload_json" => $request->payload_json,
            "attachment_media_id" => $request->attachment_media_id,
        ]);

        return new FormSubmissionResource(
            $submission->load("form", "attachmentMedia"),
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(FormSubmission $formSubmission)
    {
        return new FormSubmissionResource(
            $formSubmission->load("form", "attachmentMedia"),
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FormSubmission $formSubmission)
    {
        $formSubmission->delete();
        return response()->json(null, 204);
    }

    /**
     * Build validation rules from form schema.
     * This is a simplified example; a real implementation might be more complex.
     */
    protected function buildValidationRules(array $schema): array
    {
        $rules = [];
        foreach ($schema["fields"] ?? [] as $field) {
            $fieldRules = [];
            if (isset($field["required"]) && $field["required"]) {
                $fieldRules[] = "required";
            } else {
                $fieldRules[] = "nullable";
            }

            if (isset($field["type"])) {
                switch ($field["type"]) {
                    case "email":
                        $fieldRules[] = "email";
                        break;
                    case "number":
                        $fieldRules[] = "numeric";
                        break;
                    case "string":
                    default:
                        $fieldRules[] = "string";
                        break;
                }
            }
            if (isset($field["max"])) {
                $fieldRules[] = "max:" . $field["max"];
            }
            // Add more validation rules as needed based on your schema definition
            $rules[$field["name"]] = $fieldRules;
        }
        return $rules;
    }
}
