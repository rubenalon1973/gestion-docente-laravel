<?php

namespace Database\Seeders;

use App\Models\Availability;
use App\Models\PricingUnit;
use App\Models\Qualification;
use App\Models\Service;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherServicePrice;
use App\Models\TeacherTool;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = collect([
            'Administración',
            'Informática',
            'Inglés',
            'Matemáticas',
            'Prevención de riesgos laborales',
        ])->mapWithKeys(fn (string $name) => [
            $name => Subject::query()->firstOrCreate(
                ['name' => $name],
                ['description' => "Materia ficticia de demostración: {$name}."],
            ),
        ]);

        $service = Service::query()->where('code', 'training_delivery')->firstOrFail();
        $unit = PricingUnit::query()->where('code', 'hour')->firstOrFail();

        collect([
            ['first_name' => 'Lucía', 'last_names' => 'Martín Vega', 'email' => 'lucia.martin@example.test', 'subject' => 'Administración'],
            ['first_name' => 'Daniel', 'last_names' => 'Santos Ruiz', 'email' => 'daniel.santos@example.test', 'subject' => 'Informática'],
            ['first_name' => 'Elena', 'last_names' => 'Navarro Gil', 'email' => 'elena.navarro@example.test', 'subject' => 'Inglés'],
            ['first_name' => 'Mario', 'last_names' => 'Ortega León', 'email' => 'mario.ortega@example.test', 'subject' => 'Matemáticas'],
            ['first_name' => 'Sara', 'last_names' => 'Campos Vidal', 'email' => 'sara.campos@example.test', 'subject' => 'Prevención de riesgos laborales'],
        ])->each(function (array $profile, int $index) use ($service, $subjects, $unit): void {
            if (Teacher::query()->where('email', $profile['email'])->exists()) {
                return;
            }

            $teacher = Teacher::factory()->create([
                'first_name' => $profile['first_name'],
                'last_names' => $profile['last_names'],
                'identity_document' => sprintf('DEMO%04d', $index + 1),
                'email' => $profile['email'],
                'status' => $index === 4 ? 'inactive' : 'active',
                'internal_notes' => 'Perfil completamente ficticio creado para demostrar la aplicación.',
            ]);

            $subject = $subjects->get($profile['subject']);
            $teacher->subjects()->attach($subject, ['details' => 'Especialidad ficticia de demostración.']);

            Availability::factory()->count(2)->for($teacher)->create();
            Qualification::factory()->for($teacher)->create();
            TeacherTool::factory()->count(2)->for($teacher)->create();

            TeacherServicePrice::factory()->create([
                'teacher_id' => $teacher->id,
                'service_id' => $service->id,
                'subject_id' => $subject->id,
                'pricing_unit_id' => $unit->id,
                'net_amount' => 35 + ($index * 5),
                'valid_from' => now()->startOfYear()->toDateString(),
                'valid_until' => null,
            ]);
        });
    }
}
