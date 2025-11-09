<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\FormSubmissionController;

// Routes that require authentication
Route::middleware("auth:sanctum")->group(function () {
    Route::get("/user", function (Request $request) {
        return $request->user();
    });

    Route::get("/users/{id}/permissions", [
        PermissionController::class,
        "getPermissions",
    ])->middleware("can:gestionarUsuarios");

    Route::apiResource("permissions", PermissionController::class)->middleware(
        "can:gestionarPermisos",
    );
    Route::apiResource("roles", RoleController::class)->middleware(
        "can:gestionarRoles",
    );
    Route::get("/roles/{role}/permissions", [
        RoleController::class,
        "getPermissions",
    ])->middleware("can:gestionarRoles");
    Route::apiResource("users", UserController::class)->middleware(
        "can:gestionarUsuarios",
    );
    Route::apiResource("media", MediaController::class)->middleware(
        "can:gestionarMedios",
    );
    Route::apiResource("categories", CategoryController::class)->middleware(
        "can:gestionarCategorias",
    );
    Route::apiResource("tags", TagController::class)->middleware(
        "can:gestionarEtiquetas",
    );
    Route::apiResource(
        "publications",
        PublicationController::class,
    )->middleware("can:gestionarPublicaciones");
    Route::apiResource("events", EventController::class)->middleware(
        "can:gestionarEventos",
    );
    Route::apiResource("faculties", FacultyController::class)->middleware(
        "can:gestionarFacultades",
    );
    Route::apiResource("programs", ProgramController::class)->middleware(
        "can:gestionarProgramas",
    );
    Route::apiResource("people", PersonController::class)->middleware(
        "can:gestionarPersonas",
    );
    Route::apiResource("forms", FormController::class)->middleware(
        "can:gestionarFormularios",
    );
    Route::apiResource(
        "form-submissions",
        FormSubmissionController::class,
    )->middleware("can:gestionarEnviosFormulario");
});

// Authentication routes (login, register, etc.) - accessible to guests
require __DIR__ . "/auth.php";
