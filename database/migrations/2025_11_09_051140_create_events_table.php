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
        Schema::create("events", function (Blueprint $table) {
            $table->id();
            $table->string("title", 200);
            $table->string("slug", 220)->unique();
            $table->text("summary")->nullable();
            $table->text("content_html")->nullable();
            $table->string("location", 200)->nullable();
            $table->timestamp("starts_at");
            $table->timestamp("ends_at")->nullable();
            $table->string("status", 20)->default("draft"); // draft|review|published|archived
            $table
                ->foreignId("banner_media_id")
                ->nullable()
                ->constrained("media")
                ->onDelete("set null");
            $table
                ->foreignId("created_by")
                ->nullable()
                ->constrained("users")
                ->onDelete("set null");
            $table
                ->foreignId("updated_by")
                ->nullable()
                ->constrained("users")
                ->onDelete("set null");
            $table->timestamps();
            $table->boolean("soft_deleted")->default(false); // Using a boolean for soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("events");
    }
};
