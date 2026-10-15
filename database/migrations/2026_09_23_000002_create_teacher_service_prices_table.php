<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_service_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->restrictOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('pricing_unit_id')->constrained()->restrictOnDelete();
            $table->string('modality', 20)->default('all');
            $table->decimal('net_amount', 10, 2);
            $table->char('currency', 3)->default('EUR');
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->timestamps();

            $table->index(
                ['teacher_id', 'service_id', 'subject_id', 'modality', 'pricing_unit_id', 'valid_from'],
                'teacher_prices_scope_start_idx'
            );
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE teacher_service_prices ADD CONSTRAINT teacher_prices_modality_check CHECK (modality IN ('in_person', 'online', 'all'))");
            DB::statement('ALTER TABLE teacher_service_prices ADD CONSTRAINT teacher_prices_amount_check CHECK (net_amount >= 0)');
            DB::statement("ALTER TABLE teacher_service_prices ADD CONSTRAINT teacher_prices_currency_check CHECK (currency = 'EUR')");
            DB::statement('ALTER TABLE teacher_service_prices ADD CONSTRAINT teacher_prices_dates_check CHECK (valid_until IS NULL OR valid_until >= valid_from)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_service_prices');
    }
};
