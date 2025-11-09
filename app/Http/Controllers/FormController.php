<?php

namespace App\Http\Controllers;

use App\Http\Resources\FormResource;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class FormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $forms = Form::with("creator")->paginate();
        return FormResource::collection($forms);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "name" => ["required", "string", "max:160"],
            "code" => ["required", "string", "max:80", Rule::unique("forms")],
            "schema_json" => ["required", "json"],
            "is_active" => ["boolean"],
        ]);

        $form = Form::create(
            array_merge($validated, [
                "is_active" => $validated["is_active"] ?? true,
                "created_by" => Auth::id(),
            ]),
        );

        return new FormResource($form->load("creator"));
    }

    /**
     * Display the specified resource.
     */
    public function show(Form $form)
    {
        return new FormResource($form->load("creator"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Form $form)
    {
        $validated = $request->validate([
            "name" => ["sometimes", "required", "string", "max:160"],
            "code" => [
                "sometimes",
                "required",
                "string",
                "max:80",
                Rule::unique("forms")->ignore($form->id),
            ],
            "schema_json" => ["sometimes", "required", "json"],
            "is_active" => ["sometimes", "boolean"],
        ]);

        $form->update($validated);

        return new FormResource($form->load("creator"));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Form $form)
    {
        $form->delete();
        return response()->json(null, 204);
    }
}
