<?php

namespace Tests\Feature\Database\Migrations;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TeacherIdentityFieldsMigrationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_migration_is_reversible(): void
    {
        $migration = require database_path(
            'migrations/2026_09_28_171621_add_identity_document_and_internal_notes_to_teachers_table.php',
        );

        $this->assertTrue(Schema::hasColumns('teachers', ['identity_document', 'internal_notes']));

        $migration->down();

        $this->assertFalse(Schema::hasColumn('teachers', 'identity_document'));
        $this->assertFalse(Schema::hasColumn('teachers', 'internal_notes'));

        $migration->up();

        $this->assertTrue(Schema::hasColumns('teachers', ['identity_document', 'internal_notes']));
    }
}
