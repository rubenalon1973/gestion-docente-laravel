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
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('postal_code', 32)
                ->nullable()
                ->after('locality');
            $table->string('province', 150)
                ->nullable()
                ->after('postal_code');
            $table->string('country', 150)
                ->nullable()
                ->after('province');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn(['postal_code', 'province', 'country']);
        });
    }
};
