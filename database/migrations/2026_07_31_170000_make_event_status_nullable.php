<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('status')->nullable()->default('Proximos')->change();
            $table->string('status_label')->nullable()->default('Próximo')->change();
            $table->string('status_color')->nullable()->default('bg-indigo-600')->change();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('status')->nullable(false)->change();
            $table->string('status_label')->nullable(false)->change();
            $table->string('status_color')->nullable()->change();
        });
    }
};
