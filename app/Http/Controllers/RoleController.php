<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->paginate();
        return RoleResource::collection($roles);
    }

    public function store(Request $request)
    {
        $request->validate([
            "name" => ["required", "string", "max:255", Rule::unique("roles")],
            'description' => ['nullable', 'string'],
            'permission_ids' => ['sometimes', 'array'],
            'permission_ids.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create($request->only(['name', 'description']));

        if ($request->has('permission_ids')) {
            $role->permissions()->attach($request->input('permission_ids'));
        }

        return new RoleResource($role->load('permissions'));
    }

    public function show(Role $role)
    {
        return new RoleResource($role->load('permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            "name" => [
                "sometimes", // Changed to sometimes for PATCH semantics
                "required",
                "string",
                "max:255",
                Rule::unique("roles")->ignore($role->id),
            ],
            'description' => ['sometimes', 'nullable', 'string'],
            'permission_ids' => ['sometimes', 'array'],
            'permission_ids.*' => ['exists:permissions,id'],
        ]);

        $role->update($request->only(['name', 'description']));

        if ($request->has('permission_ids')) {
            $role->permissions()->sync($request->input('permission_ids'));
        }

        return new RoleResource($role->load('permissions'));
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(null, 204);
    }

    public function getPermissions(Role $role)
    {
        return response()->json($role->permissions);
    }
}
