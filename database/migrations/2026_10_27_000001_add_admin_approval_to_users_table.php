<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('password');
            $table->boolean('is_approved')->default(false)->after('is_admin');
        });

        // Existing accounts belonged to the previous administrative system.
        // New registrations remain pending until an administrator reviews them.
        \DB::table('users')->update([
            'is_admin' => true,
            'is_approved' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_admin', 'is_approved']);
        });
    }
};
