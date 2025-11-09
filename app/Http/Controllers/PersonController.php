<?php

namespace App\Http\Controllers;

use App\Http\Resources\PersonResource;
use App\Http\Resources\MediaResource;
use App\Http\Resources\ProgramResource;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class PersonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $people = Person::with("photoMedia", "programs")->paginate();
        return PersonResource::collection($people);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "full_name" => ["required", "string", "max:200"],
            "position_title" => ["nullable", "string", "max:160"],
            "email" => ["nullable", "string", "email", "max:160"],
            "phone" => ["nullable", "string", "max:60"],
            "biography" => ["nullable", "string"],
            "photo_media_id" => ["nullable", "exists:media,id"],
            "program_ids" => ["array"],
            "program_ids.*" => ["exists:programs,id"],
        ]);

        $person = Person::create($validated);

        if (isset($validated["program_ids"])) {
            $person->programs()->sync($validated["program_ids"]);
        }

        return new PersonResource($person->load("photoMedia", "programs"));
    }

    /**
     * Display the specified resource.
     */
    public function show(Person $person)
    {
        return new PersonResource($person->load("photoMedia", "programs"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Person $person)
    {
        $validated = $request->validate([
            "full_name" => ["sometimes", "required", "string", "max:200"],
            "position_title" => ["sometimes", "nullable", "string", "max:160"],
            "email" => ["sometimes", "nullable", "string", "email", "max:160"],
            "phone" => ["sometimes", "nullable", "string", "max:60"],
            "biography" => ["sometimes", "nullable", "string"],
            "photo_media_id" => ["sometimes", "nullable", "exists:media,id"],
            "program_ids" => ["sometimes", "array"],
            "program_ids.*" => ["exists:programs,id"],
        ]);

        $person->update($validated);

        if (isset($validated["program_ids"])) {
            $person->programs()->sync($validated["program_ids"]);
        } elseif (
            array_key_exists("program_ids", $validated) &&
            empty($validated["program_ids"])
        ) {
            $person->programs()->detach();
        }

        return new PersonResource($person->load("photoMedia", "programs"));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Person $person)
    {
        $person->delete();
        return response()->json(null, 204);
    }
}
