<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define feature-based permissions
        $manageUsersPermission = Permission::firstOrCreate([
            "name" => "gestionarUsuarios",
        ]);
        $manageOwnProfilePermission = Permission::firstOrCreate([
            "name" => "gestionarPerfilPropio",
        ]);
        $managePermissionsPermission = Permission::firstOrCreate([
            "name" => "gestionarPermisos",
        ]);
        $manageRolesPermission = Permission::firstOrCreate([
            "name" => "gestionarRoles",
        ]);
        $manageMediaPermission = Permission::firstOrCreate([
            "name" => "gestionarMedios",
        ]);
        $manageCategoriesPermission = Permission::firstOrCreate([
            "name" => "gestionarCategorias",
        ]);
        $manageTagsPermission = Permission::firstOrCreate([
            "name" => "gestionarEtiquetas",
        ]);
        $managePublicationsPermission = Permission::firstOrCreate([
            "name" => "gestionarPublicaciones",
        ]);
        $manageEventsPermission = Permission::firstOrCreate([
            "name" => "gestionarEventos",
        ]);
        $manageFacultiesPermission = Permission::firstOrCreate([
            "name" => "gestionarFacultades",
        ]);
        $manageProgramsPermission = Permission::firstOrCreate([
            "name" => "gestionarProgramas",
        ]);
        $managePeoplePermission = Permission::firstOrCreate([
            "name" => "gestionarPersonas",
        ]);
        $manageFormsPermission = Permission::firstOrCreate([
            "name" => "gestionarFormularios",
        ]);
        $manageFormSubmissionsPermission = Permission::firstOrCreate([
            "name" => "gestionarEnviosFormulario",
        ]);

        // Assign all relevant permissions to the 'Administrador' role
        $adminRole = Role::where("name", "Administrador")->first();

        if ($adminRole) {
            $adminRole
                ->permissions()
                ->syncWithoutDetaching([
                    $manageUsersPermission->id,
                    $managePermissionsPermission->id,
                    $manageRolesPermission->id,
                    $manageMediaPermission->id,
                    $manageCategoriesPermission->id,
                    $manageTagsPermission->id,
                    $managePublicationsPermission->id,
                    $manageEventsPermission->id,
                    $manageFacultiesPermission->id,
                    $manageProgramsPermission->id,
                    $managePeoplePermission->id,
                    $manageFormsPermission->id,
                    $manageFormSubmissionsPermission->id,
                ]);
        }
    }
}
