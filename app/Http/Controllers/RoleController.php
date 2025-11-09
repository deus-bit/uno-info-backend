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
        $roles = Role::paginate();
        return RoleResource::collection($roles);
    }

    public function store(Request $request)
    {
        $request->validate([
            "name" => ["required", "string", "max:255", Rule::unique("roles")],
        ]);

        $role = Role::create($request->all());
        return new RoleResource($role);
    }

    public function show(Role $role)
    {
        return new RoleResource($role);
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            "name" => [
                "required",
                "string",
                "max:255",
                Rule::unique("roles")->ignore($role->id),
            ],
        ]);

        $role->update($request->all());
        return new RoleResource($role);
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
