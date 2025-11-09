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
        Schema::create("people", function (Blueprint $table) {
            $table->id();
            $table->string("full_name", 200);
            $table->string("position_title", 160)->nullable();
            $table->string("email", 160)->nullable();
            $table->string("phone", 60)->nullable();
            $table->text("biography")->nullable();
            $table
                ->foreignId("photo_media_id")
                ->nullable()
                ->constrained("media")
                ->onDelete("set null");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("people");
    }
};
