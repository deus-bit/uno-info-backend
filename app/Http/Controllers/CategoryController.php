<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::paginate();
        return CategoryResource::collection($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "name" => ["required", "string", "max:120"],
            "slug" => [
                "nullable",
                "string",
                "max:140",
                Rule::unique("categories"),
            ],
        ]);

        $slug = $request->slug ?? Str::slug($request->name);

        $category = Category::create([
            "name" => $request->name,
            "slug" => $slug,
        ]);

        return new CategoryResource($category);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            "name" => ["required", "string", "max:120"],
            "slug" => [
                "nullable",
                "string",
                "max:140",
                Rule::unique("categories")->ignore($category->id),
            ],
        ]);

        $slug = $request->slug ?? Str::slug($request->name);

        $category->update([
            "name" => $request->name,
            "slug" => $slug,
        ]);

        return new CategoryResource($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(null, 204);
    }
}
