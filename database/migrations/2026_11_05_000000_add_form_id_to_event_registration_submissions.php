<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_registration_submissions', function (Blueprint $table) {
            $table->string('form_id', 64)->nullable()->after('event_id')->index();
        });
        DB::table('event_registration_submissions')->whereNull('form_id')->update(['form_id' => 'default']);
    }

    public function down(): void
    {
        Schema::table('event_registration_submissions', function (Blueprint $table) {
            $table->dropIndex(['form_id']);
            $table->dropColumn('form_id');
        });
    }
};
