<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->text('fb_link')->nullable()->change();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->text('file_path')->nullable()->change();
        });

        Schema::table('team_members', function (Blueprint $table) {
            $table->text('image_path')->nullable()->change();
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->text('youtube_url')->change();
            $table->text('embed_url')->change();
            $table->text('thumbnail_url')->nullable()->change();
        });

        Schema::table('sub_units', function (Blueprint $table) {
            $table->text('logo_path')->nullable()->change();
            $table->text('fb_url')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('fb_link', 255)->nullable()->change();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->string('file_path', 255)->nullable()->change();
        });

        Schema::table('team_members', function (Blueprint $table) {
            $table->string('image_path', 255)->nullable()->change();
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->string('youtube_url', 255)->change();
            $table->string('embed_url', 255)->change();
            $table->string('thumbnail_url', 255)->nullable()->change();
        });

        Schema::table('sub_units', function (Blueprint $table) {
            $table->string('logo_path', 255)->nullable()->change();
            $table->string('fb_url', 255)->nullable()->change();
        });
    }
};
