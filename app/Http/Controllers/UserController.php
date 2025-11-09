<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->paginate();
        return UserResource::collection($users);
    }

    public function store(Request $request)
    {
        $request->validate([
            "name" => ["required", "string", "max:255"],
            "email" => [
                "required",
                "string",
                "email",
                "max:255",
                Rule::unique("users"),
            ],
            "password" => ["required", "string", "min:8"],
            'role_ids' => ['sometimes', 'array'],
            'role_ids.*' => ['exists:roles,id'],
        ]);

        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
        ]);

        if ($request->has('role_ids')) {
            $user->roles()->attach($request->input('role_ids'));
        }

        return new UserResource($user->load('roles'));
    }

    public function show(User $user)
    {
        return new UserResource($user->load('roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            "name" => ["sometimes", "required", "string", "max:255"],
            "email" => [
                "sometimes",
                "required",
                "string",
                "email",
                "max:255",
                Rule::unique("users")->ignore($user->id),
            ],
            "password" => ["sometimes", "required", "string", "min:8"],
            'role_ids' => ['sometimes', 'array'],
            'role_ids.*' => ['exists:roles,id'],
        ]);

        $userData = $request->only(["name", "email"]);
        if ($request->has("password")) {
            $userData["password"] = Hash::make($request->password);
        }

        $user->update($userData);

        if ($request->has('role_ids')) {
            $user->roles()->sync($request->input('role_ids'));
        }

        return new UserResource($user->load('roles'));
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }
}
