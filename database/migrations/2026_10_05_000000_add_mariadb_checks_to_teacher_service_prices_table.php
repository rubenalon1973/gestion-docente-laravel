<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** @var array<string, string> */
    private const CONSTRAINTS = [
        'teacher_prices_modality_check' => "modality IN ('in_person', 'online', 'all')",
        'teacher_prices_amount_check' => 'net_amount >= 0',
        'teacher_prices_currency_check' => "currency = 'EUR'",
        'teacher_prices_dates_check' => 'valid_until IS NULL OR valid_until >= valid_from',
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'mariadb') {
            return;
        }

        foreach (self::CONSTRAINTS as $name => $expression) {
            if (! $this->constraintExists($name)) {
                DB::statement("ALTER TABLE teacher_service_prices ADD CONSTRAINT {$name} CHECK ({$expression})");
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mariadb') {
            return;
        }

        foreach (array_keys(self::CONSTRAINTS) as $name) {
            if ($this->constraintExists($name)) {
                DB::statement("ALTER TABLE teacher_service_prices DROP CONSTRAINT {$name}");
            }
        }
    }

    private function constraintExists(string $name): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::connection()->getDatabaseName())
            ->where('TABLE_NAME', 'teacher_service_prices')
            ->where('CONSTRAINT_NAME', $name)
            ->where('CONSTRAINT_TYPE', 'CHECK')
            ->exists();
    }
};
