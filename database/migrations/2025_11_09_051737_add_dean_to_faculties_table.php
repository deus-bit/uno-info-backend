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
        Schema::table("faculties", function (Blueprint $table) {
            $table
                ->foreignId("dean_person_id")
                ->nullable()
                ->constrained("people")
                ->onDelete("set null");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("faculties", function (Blueprint $table) {
            $table->dropConstrainedForeignId("dean_person_id");
        });
    }
};
