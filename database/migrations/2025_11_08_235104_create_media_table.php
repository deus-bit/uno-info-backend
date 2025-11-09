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
        Schema::create("media", function (Blueprint $table) {
            $table->id();
            $table->string("file_name", 200);
            $table->string("mime_type", 100);
            $table->text("url");
            $table->bigInteger("size_bytes")->nullable();
            $table->string("alt_text", 255)->nullable();
            $table
                ->foreignId("uploaded_by")
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
        Schema::dropIfExists("media");
    }
};
