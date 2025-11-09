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
        Schema::create("form_submissions", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("form_id")
                ->constrained("forms")
                ->onDelete("cascade");
            $table->timestamp("submitted_at")->useCurrent();
            $table->string("ip_address", 64)->nullable();
            $table->json("payload_json"); // Stores the submitted data
            $table
                ->foreignId("attachment_media_id")
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
        Schema::dropIfExists("form_submissions");
    }
};
