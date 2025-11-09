<?php

namespace App\Http\Controllers;

use App\Http\Resources\PublicationResource;
use App\Models\Publication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PublicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $publications = Publication::with(["category", "tags"])->paginate();
        return PublicationResource::collection($publications);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "title" => ["required", "string", "max:200"],
            "slug" => [
                "nullable",
                "string",
                "max:220",
                Rule::unique("publications"),
            ],
            "summary" => ["nullable", "string"],
            "content_html" => ["nullable", "string"],
            "category_id" => ["nullable", "exists:categories,id"],
            "status" => [
                "required",
                "string",
                Rule::in(["draft", "review", "published", "archived"]),
            ],
            "published_at" => ["nullable", "date"],
            "cover_media_id" => ["nullable", "exists:media,id"],
            "featured" => ["boolean"],
            "tags" => ["array"],
            "tags.*" => ["exists:tags,id"],
        ]);

        $slug = $validated["slug"] ?? Str::slug($validated["title"]);

        $publication = Publication::create(
            array_merge($validated, [
                "slug" => $slug,
                "created_by" => Auth::id(),
                "updated_by" => Auth::id(),
                "soft_deleted" => false,
            ]),
        );

        if (isset($validated["tags"])) {
            $publication->tags()->sync($validated["tags"]);
        }

        return new PublicationResource(
            $publication->load(["category", "tags"]),
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Publication $publication)
    {
        return new PublicationResource(
            $publication->load(["category", "tags"]),
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Publication $publication)
    {
        $validated = $request->validate([
            "title" => ["sometimes", "required", "string", "max:200"],
            "slug" => [
                "sometimes",
                "nullable",
                "string",
                "max:220",
                Rule::unique("publications")->ignore($publication->id),
            ],
            "summary" => ["sometimes", "nullable", "string"],
            "content_html" => ["sometimes", "nullable", "string"],
            "category_id" => ["sometimes", "nullable", "exists:categories,id"],
            "status" => [
                "sometimes",
                "required",
                "string",
                Rule::in(["draft", "review", "published", "archived"]),
            ],
            "published_at" => ["sometimes", "nullable", "date"],
            "cover_media_id" => ["sometimes", "nullable", "exists:media,id"],
            "featured" => ["sometimes", "boolean"],
            "tags" => ["sometimes", "array"],
            "tags.*" => ["exists:tags,id"],
        ]);

        $updateData = $validated;
        if (isset($validated["title"]) && !isset($validated["slug"])) {
            $updateData["slug"] = Str::slug($validated["title"]);
        }
        $updateData["updated_by"] = Auth::id();

        $publication->update($updateData);

        if (isset($validated["tags"])) {
            $publication->tags()->sync($validated["tags"]);
        }

        return new PublicationResource(
            $publication->load(["category", "tags"]),
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Publication $publication)
    {
        $publication->update(["soft_deleted" => true]);
        return response()->json(null, 204);
    }
}
