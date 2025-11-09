<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProgramResource;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ProgramController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $programs = Program::with("faculty", "people")->paginate();
        return ProgramResource::collection($programs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "faculty_id" => ["nullable", "exists:faculties,id"],
            "name" => ["required", "string", "max:200"],
            "slug" => [
                "nullable",
                "string",
                "max:220",
                Rule::unique("programs"),
            ],
            "degree_type" => ["nullable", "string", "max:80"],
            "duration_semesters" => ["nullable", "integer"],
            "modality" => ["nullable", "string", "max:60"],
            "description" => ["nullable", "string"],
            "person_ids" => ["array"],
            "person_ids.*" => ["exists:people,id"],
        ]);

        $slug = $validated["slug"] ?? Str::slug($validated["name"]);

        $program = Program::create(array_merge($validated, ["slug" => $slug]));

        if (isset($validated["person_ids"])) {
            $program->people()->sync($validated["person_ids"]);
        }

        return new ProgramResource($program->load("faculty", "people"));
    }

    /**
     * Display the specified resource.
     */
    public function show(Program $program)
    {
        return new ProgramResource($program->load("faculty", "people"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Program $program)
    {
        $validated = $request->validate([
            "faculty_id" => ["sometimes", "nullable", "exists:faculties,id"],
            "name" => ["sometimes", "required", "string", "max:200"],
            "slug" => [
                "sometimes",
                "nullable",
                "string",
                "max:220",
                Rule::unique("programs")->ignore($program->id),
            ],
            "degree_type" => ["sometimes", "nullable", "string", "max:80"],
            "duration_semesters" => ["sometimes", "nullable", "integer"],
            "modality" => ["sometimes", "nullable", "string", "max:60"],
            "description" => ["sometimes", "nullable", "string"],
            "person_ids" => ["sometimes", "array"],
            "person_ids.*" => ["exists:people,id"],
        ]);

        $updateData = $validated;
        if (isset($validated["name"]) && !isset($validated["slug"])) {
            $updateData["slug"] = Str::slug($validated["name"]);
        }

        $program->update($updateData);

        if (isset($validated["person_ids"])) {
            $program->people()->sync($validated["person_ids"]);
        } elseif (
            array_key_exists("person_ids", $validated) &&
            empty($validated["person_ids"])
        ) {
            $program->people()->detach();
        }

        return new ProgramResource($program->load("faculty", "people"));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Program $program)
    {
        $program->delete();
        return response()->json(null, 204);
    }
}
