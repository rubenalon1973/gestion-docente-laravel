<?php

namespace Tests\Feature\Database\Migrations;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TeacherContactFieldsMigrationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_migration_is_reversible(): void
    {
        $migration = require database_path(
            'migrations/2026_09_28_181037_add_contact_location_fields_to_teachers_table.php',
        );

        $columns = ['postal_code', 'province', 'country'];

        $this->assertTrue(Schema::hasColumns('teachers', $columns));

        $migration->down();

        foreach ($columns as $column) {
            $this->assertFalse(Schema::hasColumn('teachers', $column));
        }

        $migration->up();

        $this->assertTrue(Schema::hasColumns('teachers', $columns));
    }
}
