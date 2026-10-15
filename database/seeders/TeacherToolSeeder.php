<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\TeacherTool;
use Illuminate\Database\Seeder;

class TeacherToolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Teacher::query()->each(function (Teacher $teacher): void {
            TeacherTool::factory()
                ->count(3)
                ->for($teacher)
                ->create();
        });
    }
}
