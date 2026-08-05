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
        // 1. Table for custom fonts
        Schema::create('certificate_fonts', function (Blueprint $table) {
            $table->id();
            $table->string('font_name');
            $table->string('file_path');
            $table->timestamps();
        });

        // 2. Table for certificate templates
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('background_path');
            $table->json('settings'); // Store font family, positions, size, colors
            $table->timestamps();
        });

        // 3. Table for certificate records (issued on-demand)
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certificate_template_id')->constrained('certificate_templates')->onDelete('cascade');
            $table->uuid('uuid')->unique();
            $table->string('code')->unique();
            $table->string('recipient_name');
            $table->string('recipient_document'); // DNI or Student Code
            $table->string('recipient_email')->nullable();
            $table->string('role')->nullable(); // e.g., Ponente, Organizador, Participante
            $table->date('issue_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('certificate_templates');
        Schema::dropIfExists('certificate_fonts');
    }
};
