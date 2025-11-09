<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where("name", "Administrador")->first();

        User::firstOrCreate(
            ["email" => "admin@example.com"],
            [
                "name" => "Admin User",
                "password" => Hash::make("password"),
            ],
        )
            ->roles()
            ->syncWithoutDetaching($adminRole->id);
    }
}
