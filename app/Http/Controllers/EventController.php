<?php

namespace App\Http\Controllers;

use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = Event::with([
            "bannerMedia",
            "creator",
            "updater",
            "tags",
        ])->paginate();
        return EventResource::collection($events);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "title" => ["required", "string", "max:200"],
            "slug" => ["nullable", "string", "max:220", Rule::unique("events")],
            "summary" => ["nullable", "string"],
            "content_html" => ["nullable", "string"],
            "location" => ["nullable", "string", "max:200"],
            "starts_at" => ["required", "date"],
            "ends_at" => ["nullable", "date", "after_or_equal:starts_at"],
            "status" => [
                "required",
                "string",
                Rule::in(["draft", "review", "published", "archived"]),
            ],
            "banner_media_id" => ["nullable", "exists:media,id"],
            "tags" => ["array"],
            "tags.*" => ["exists:tags,id"],
        ]);

        $slug = $validated["slug"] ?? Str::slug($validated["title"]);

        $event = Event::create(
            array_merge($validated, [
                "slug" => $slug,
                "created_by" => Auth::id(),
                "updated_by" => Auth::id(),
                "soft_deleted" => false,
            ]),
        );

        if (isset($validated["tags"])) {
            $event->tags()->sync($validated["tags"]);
        }

        return new EventResource(
            $event->load(["bannerMedia", "creator", "updater", "tags"]),
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        return new EventResource(
            $event->load(["bannerMedia", "creator", "updater", "tags"]),
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            "title" => ["sometimes", "required", "string", "max:200"],
            "slug" => [
                "sometimes",
                "nullable",
                "string",
                "max:220",
                Rule::unique("events")->ignore($event->id),
            ],
            "summary" => ["sometimes", "nullable", "string"],
            "content_html" => ["sometimes", "nullable", "string"],
            "location" => ["sometimes", "nullable", "string", "max:200"],
            "starts_at" => ["sometimes", "required", "date"],
            "ends_at" => [
                "sometimes",
                "nullable",
                "date",
                "after_or_equal:starts_at",
            ],
            "status" => [
                "sometimes",
                "required",
                "string",
                Rule::in(["draft", "review", "published", "archived"]),
            ],
            "banner_media_id" => ["sometimes", "nullable", "exists:media,id"],
            "tags" => ["sometimes", "array"],
            "tags.*" => ["exists:tags,id"],
        ]);

        $updateData = $validated;
        if (isset($validated["title"]) && !isset($validated["slug"])) {
            $updateData["slug"] = Str::slug($validated["title"]);
        }
        $updateData["updated_by"] = Auth::id();

        $event->update($updateData);

        if (isset($validated["tags"])) {
            $event->tags()->sync($validated["tags"]);
        }

        return new EventResource(
            $event->load(["bannerMedia", "creator", "updater", "tags"]),
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->update(["soft_deleted" => true]);
        return response()->json(null, 204);
    }
}
