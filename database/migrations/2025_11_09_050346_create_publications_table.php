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
        Schema::create("publications", function (Blueprint $table) {
            $table->id();
            $table->string("title", 200);
            $table->string("slug", 220)->unique();
            $table->text("summary")->nullable();
            $table->text("content_html")->nullable();
            $table
                ->foreignId("category_id")
                ->nullable()
                ->constrained("categories")
                ->onDelete("set null");
            $table->string("status", 20)->default("draft"); // draft|review|published|archived
            $table->timestamp("published_at")->nullable();
            $table
                ->foreignId("cover_media_id")
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
            $table->boolean("featured")->default(false);
            $table->timestamps();
            $table->boolean("soft_deleted")->default(false); // Using a boolean for soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("publications");
    }
};
