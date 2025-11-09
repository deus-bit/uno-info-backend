<?php

namespace App\Http\Controllers;

use App\Http\Resources\PermissionResource;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::paginate();
        return PermissionResource::collection($permissions);
    }

    public function store(Request $request)
    {
        $request->validate([
            "name" => [
                "required",
                "string",
                "max:255",
                Rule::unique("permissions"),
            ],
        ]);

        $permission = Permission::create($request->all());
        return new PermissionResource($permission);
    }

    public function show(Permission $permission)
    {
        return new PermissionResource($permission);
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            "name" => [
                "required",
                "string",
                "max:255",
                Rule::unique("permissions")->ignore($permission->id),
            ],
        ]);

        $permission->update($request->all());
        return new PermissionResource($permission);
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return response()->json(null, 204);
    }

    public function getPermissions(Request $request, $id)
    {
        $user = User::with("roles.permissions")->find($id);

        if (!$user) {
            return response()->json(["message" => "User not found"], 404);
        }

        $permissions = $user->roles
            ->flatMap(function ($role) {
                return $role->permissions;
            })
            ->unique("id")
            ->values();

        return response()->json($permissions);
    }
}
