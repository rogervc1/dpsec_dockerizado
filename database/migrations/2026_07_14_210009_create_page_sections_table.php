<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->string('page_slug');
            $table->string('section_key');
            $table->string('eyebrow')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('background_image')->nullable();
            $table->json('extra_data')->nullable();
            $table->timestamps();

            $table->unique(['page_slug', 'section_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_sections');
    }
};
