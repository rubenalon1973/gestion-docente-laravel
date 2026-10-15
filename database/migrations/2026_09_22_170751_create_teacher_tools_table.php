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
        Schema::create('teacher_tools', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('category', 20);
            $table->string('tool_name', 150);
            $table->string('level', 50);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_tools');
    }
};
