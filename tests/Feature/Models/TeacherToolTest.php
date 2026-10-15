<?php

namespace Tests\Feature\Models;

use App\Models\Teacher;
use App\Models\TeacherTool;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class TeacherToolTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_teacher_exposes_associated_tools(): void
    {
        $teacher = Teacher::factory()->create();

        $teacherTool = TeacherTool::factory()
            ->for($teacher)
            ->create([
                'category' => 'software',
                'tool_name' => 'Herramienta de demostración',
                'level' => 'intermedio',
            ]);

        $teacher->refresh();

        $this->assertTrue(
            $teacher->teacherTools->contains('id', $teacherTool->id)
        );
        $this->assertTrue($teacherTool->teacher->is($teacher));
        $this->assertSame('software', $teacherTool->category);
        $this->assertSame('intermedio', $teacherTool->level);
    }
}
