<?php

namespace App\Http\Controllers;

use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tags = Tag::paginate();
        return TagResource::collection($tags);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "name" => ["required", "string", "max:120"],
            "slug" => ["nullable", "string", "max:140", Rule::unique("tags")],
        ]);

        $slug = $request->slug ?? Str::slug($request->name);

        $tag = Tag::create([
            "name" => $request->name,
            "slug" => $slug,
        ]);

        return new TagResource($tag);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag)
    {
        return new TagResource($tag);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tag $tag)
    {
        $request->validate([
            "name" => ["required", "string", "max:120"],
            "slug" => [
                "nullable",
                "string",
                "max:140",
                Rule::unique("tags")->ignore($tag->id),
            ],
        ]);

        $slug = $request->slug ?? Str::slug($request->name);

        $tag->update([
            "name" => $request->name,
            "slug" => $slug,
        ]);

        return new TagResource($tag);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        $tag->delete();
        return response()->json(null, 204);
    }
}
