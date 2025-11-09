<?php

namespace App\Http\Controllers;

use App\Http\Resources\MediaResource;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class MediaController extends Controller
{
    /**
     * Display a listing of the media.
     */
    public function index()
    {
        $media = Media::paginate();
        return MediaResource::collection($media);
    }

    /**
     * Store a newly created media in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "file" => "required|file|max:10240", // Max 10MB (10240 KB)
            "alt_text" => "nullable|string|max:255",
        ]);

        $file = $request->file("file");
        $path = $file->store("media", "public"); // Store in storage/app/public/media

        $media = Media::create([
            "file_name" => $file->getClientOriginalName(),
            "mime_type" => $file->getMimeType(),
            "url" => Storage::disk("public")->url($path),
            "size_bytes" => $file->getSize(),
            "alt_text" => $request->input("alt_text"),
            "uploaded_by" => Auth::id(), // Assuming authentication is in place
        ]);

        return new MediaResource($media);
    }

    /**
     * Display the specified media.
     */
    public function show(Media $medium)
    {
        return new MediaResource($medium);
    }

    /**
     * Update the specified media in storage.
     */
    public function update(Request $request, Media $medium)
    {
        $request->validate([
            "alt_text" => "nullable|string|max:255",
        ]);

        $medium->update([
            "alt_text" => $request->input("alt_text"),
        ]);

        return new MediaResource($medium);
    }

    /**
     * Remove the specified media from storage.
     */
    public function destroy(Media $medium)
    {
        // Extract the path relative to the public disk
        $relativePath = str_replace(
            Storage::disk("public")->url("/"),
            "",
            $medium->url,
        );

        // Delete the file from storage
        Storage::disk("public")->delete($relativePath);

        // Delete the record from the database
        $medium->delete();

        return response()->json(null, 204);
    }
}
