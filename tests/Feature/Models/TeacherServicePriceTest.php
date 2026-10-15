<?php

namespace Tests\Feature\Models;

use App\Models\PricingUnit;
use App\Models\Service;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\TeacherServicePriceService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TeacherServicePriceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_creates_a_net_price_with_its_relations(): void
    {
        [$teacher, $subject, $service, $unit] = $this->priceContext();

        $price = app(TeacherServicePriceService::class)->create([
            'teacher_id' => $teacher->id,
            'service_id' => $service->id,
            'subject_id' => $subject->id,
            'pricing_unit_id' => $unit->id,
            'modality' => 'online',
            'net_amount' => 125.50,
            'currency' => 'EUR',
            'valid_from' => '2027-01-01',
            'valid_until' => null,
        ]);

        $this->assertTrue($price->teacher->is($teacher));
        $this->assertTrue($price->service->is($service));
        $this->assertTrue($price->subject->is($subject));
        $this->assertTrue($price->pricingUnit->is($unit));
        $this->assertSame('125.50', $price->net_amount);
        $this->assertSame('EUR', $price->currency);
    }

    public function test_it_rejects_overlapping_inclusive_periods_in_the_same_scope(): void
    {
        [$teacher, $subject, $service, $unit] = $this->priceContext();
        $prices = app(TeacherServicePriceService::class);

        $prices->create($this->attributes($teacher, $subject, $service, $unit, [
            'valid_from' => '2027-01-01',
            'valid_until' => '2027-03-31',
        ]));

        $this->expectException(ValidationException::class);

        $prices->create($this->attributes($teacher, $subject, $service, $unit, [
            'valid_from' => '2027-03-31',
            'valid_until' => '2027-06-30',
        ]));
    }

    public function test_it_allows_adjacent_periods_and_prefers_the_most_specific_price(): void
    {
        [$teacher, $subject, $service, $unit] = $this->priceContext();
        $prices = app(TeacherServicePriceService::class);

        $prices->create($this->attributes($teacher, null, $service, $unit, [
            'modality' => 'all',
            'net_amount' => 80,
            'valid_from' => '2027-01-01',
            'valid_until' => null,
        ]));

        $specific = $prices->create($this->attributes($teacher, $subject, $service, $unit, [
            'modality' => 'online',
            'net_amount' => 95,
            'valid_from' => '2027-01-01',
            'valid_until' => '2027-03-31',
        ]));

        $adjacent = $prices->create($this->attributes($teacher, $subject, $service, $unit, [
            'modality' => 'online',
            'net_amount' => 100,
            'valid_from' => '2027-04-01',
            'valid_until' => null,
        ]));

        $this->assertTrue($prices->findApplicable(
            $teacher->id,
            $service->id,
            $unit->id,
            $subject->id,
            'online',
            '2027-03-15'
        )->is($specific));

        $this->assertTrue($prices->findApplicable(
            $teacher->id,
            $service->id,
            $unit->id,
            $subject->id,
            'online',
            '2027-04-01'
        )->is($adjacent));
    }

    /**
     * @return array{Teacher, Subject, Service, PricingUnit}
     */
    private function priceContext(): array
    {
        $teacher = Teacher::factory()->create();
        $subject = Subject::factory()->create();
        $service = Service::factory()->create();
        $unit = PricingUnit::factory()->create();

        $teacher->subjects()->attach($subject);

        return [$teacher, $subject, $service, $unit];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function attributes(
        Teacher $teacher,
        ?Subject $subject,
        Service $service,
        PricingUnit $unit,
        array $overrides = [],
    ): array {
        return array_replace([
            'teacher_id' => $teacher->id,
            'service_id' => $service->id,
            'subject_id' => $subject?->id,
            'pricing_unit_id' => $unit->id,
            'modality' => 'online',
            'net_amount' => 90,
            'currency' => 'EUR',
            'valid_from' => '2027-01-01',
            'valid_until' => null,
        ], $overrides);
    }
}
