<?php

namespace Tests\Feature\Database\Migrations;

use App\Models\PricingUnit;
use App\Models\Service;
use App\Models\Teacher;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TeacherServicePriceMariaDbConstraintsTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @return array<string, array{array<string, mixed>}> */
    public static function invalidValues(): array
    {
        return [
            'modalidad' => [['modality' => 'invalid']],
            'importe' => [['net_amount' => -1]],
            'moneda' => [['currency' => 'USD']],
            'fechas' => [['valid_from' => '2026-10-05', 'valid_until' => '2026-10-04']],
        ];
    }

    public function test_mariadb_has_the_four_named_check_constraints(): void
    {
        $this->requireMariaDb();

        $constraints = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::connection()->getDatabaseName())
            ->where('TABLE_NAME', 'teacher_service_prices')
            ->where('CONSTRAINT_TYPE', 'CHECK')
            ->pluck('CONSTRAINT_NAME');

        $this->assertEqualsCanonicalizing([
            'teacher_prices_modality_check',
            'teacher_prices_amount_check',
            'teacher_prices_currency_check',
            'teacher_prices_dates_check',
        ], $constraints->all());
    }

    #[DataProvider('invalidValues')]
    public function test_mariadb_rejects_values_for_each_check_constraint(array $invalidValues): void
    {
        $this->requireMariaDb();

        $values = array_merge([
            'teacher_id' => Teacher::factory()->create()->id,
            'service_id' => Service::factory()->create()->id,
            'subject_id' => null,
            'pricing_unit_id' => PricingUnit::factory()->create()->id,
            'modality' => 'all',
            'net_amount' => 10,
            'currency' => 'EUR',
            'valid_from' => '2026-10-05',
            'valid_until' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $invalidValues);

        $this->expectException(QueryException::class);

        DB::table('teacher_service_prices')->insert($values);
    }

    private function requireMariaDb(): void
    {
        if (DB::getDriverName() !== 'mariadb') {
            $this->markTestSkipped('Esta prueba requiere una conexión MariaDB real.');
        }
    }
}
