<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("programs", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("faculty_id")
                ->nullable()
                ->constrained("faculties")
                ->onDelete("set null");
            $table->string("name", 200);
            $table->string("slug", 220)->unique();
            $table->string("degree_type", 80)->nullable();
            $table->integer("duration_semesters")->nullable();
            $table->string("modality", 60)->nullable();
            $table->text("description")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("programs");
    }
};
