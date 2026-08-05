<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type');
            $table->string('category');
            $table->string('status');
            $table->string('status_label');
            $table->string('status_color')->nullable();
            $table->date('event_date')->nullable();
            $table->string('time')->nullable();
            $table->string('location');
            $table->string('organizer');
            $table->text('description');
            $table->text('image_path')->nullable();
            $table->string('fb_link')->nullable();
            $table->boolean('is_proyeccion_social')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
