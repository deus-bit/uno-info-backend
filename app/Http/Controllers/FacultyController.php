<?php

namespace App\Http\Controllers;

use App\Http\Resources\FacultyResource;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class FacultyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $faculties = Faculty::with("dean", "programs")->paginate();
        return FacultyResource::collection($faculties);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "name" => ["required", "string", "max:200"],
            "slug" => [
                "nullable",
                "string",
                "max:220",
                Rule::unique("faculties"),
            ],
            "description" => ["nullable", "string"],
            "dean_person_id" => ["nullable", "exists:people,id"],
        ]);

        $slug = $validated["slug"] ?? Str::slug($validated["name"]);

        $faculty = Faculty::create(array_merge($validated, ["slug" => $slug]));

        return new FacultyResource($faculty->load("dean", "programs"));
    }

    /**
     * Display the specified resource.
     */
    public function show(Faculty $faculty)
    {
        return new FacultyResource($faculty->load("dean", "programs"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Faculty $faculty)
    {
        $validated = $request->validate([
            "name" => ["sometimes", "required", "string", "max:200"],
            "slug" => [
                "sometimes",
                "nullable",
                "string",
                "max:220",
                Rule::unique("faculties")->ignore($faculty->id),
            ],
            "description" => ["sometimes", "nullable", "string"],
            "dean_person_id" => ["sometimes", "nullable", "exists:people,id"],
        ]);

        $updateData = $validated;
        if (isset($validated["name"]) && !isset($validated["slug"])) {
            $updateData["slug"] = Str::slug($validated["name"]);
        }

        $faculty->update($updateData);

        return new FacultyResource($faculty->load("dean", "programs"));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Faculty $faculty)
    {
        $faculty->delete();
        return response()->json(null, 204);
    }
}
