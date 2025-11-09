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
        Schema::create("forms", function (Blueprint $table) {
            $table->id();
            $table->string("name", 160);
            $table->string("code", 80)->unique();
            $table->json("schema_json"); // Stores the form structure and validation rules
            $table->boolean("is_active")->default(true);
            $table
                ->foreignId("created_by")
                ->nullable()
                ->constrained("users")
                ->onDelete("set null");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("forms");
    }
};
