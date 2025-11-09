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
        Schema::create("person_program", function (Blueprint $table) {
            $table
                ->foreignId("person_id")
                ->constrained("people")
                ->onDelete("cascade");
            $table
                ->foreignId("program_id")
                ->constrained("programs")
                ->onDelete("cascade");
            $table->primary(["person_id", "program_id"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("person_program");
    }
};
