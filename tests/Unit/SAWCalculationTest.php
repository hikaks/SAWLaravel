<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\SAWCalculationService;
use App\Models\Employee;
use App\Models\Criteria;
use App\Models\Evaluation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase as BaseTestCase;

class SAWCalculationTest extends BaseTestCase
{
    use RefreshDatabase;

    private SAWCalculationService $sawService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sawService = new SAWCalculationService();
    }

    /** @test */
    public function it_calculates_saw_correctly_for_benefit_criteria()
    {
        // Arrange: Create test data
        $employee1 = Employee::factory()->create(['name' => 'John Doe']);
        $employee2 = Employee::factory()->create(['name' => 'Jane Smith']);
        
        $criteria = Criteria::factory()->create([
            'name' => 'Performance',
            'weight' => 100,
            'type' => 'benefit'
        ]);

        // Employee 1: Score 100 (should get rank 1)
        Evaluation::factory()->create([
            'employee_id' => $employee1->id,
            'criteria_id' => $criteria->id,
            'score' => 100,
            'evaluation_period' => '2024-01'
        ]);

        // Employee 2: Score 80 (should get rank 2)
        Evaluation::factory()->create([
            'employee_id' => $employee2->id,
            'criteria_id' => $criteria->id,
            'score' => 80,
            'evaluation_period' => '2024-01'
        ]);

        // Act: Calculate SAW
        $results = $this->sawService->calculateSAW('2024-01');

        // Assert: Check results
        $this->assertCount(2, $results);
        
        // First result should be employee1 with score 1.0 and rank 1
        $this->assertEquals($employee1->id, $results[0]['employee_id']);
        $this->assertEquals(1.0, $results[0]['total_score']);
        $this->assertEquals(1, $results[0]['ranking']);
        
        // Second result should be employee2 with score 0.8 and rank 2
        $this->assertEquals($employee2->id, $results[1]['employee_id']);
        $this->assertEquals(0.8, $results[1]['total_score']);
        $this->assertEquals(2, $results[1]['ranking']);
    }

    /** @test */
    public function it_calculates_saw_correctly_for_cost_criteria()
    {
        // Arrange: Create test data
        $employee1 = Employee::factory()->create();
        $employee2 = Employee::factory()->create();
        
        $criteria = Criteria::factory()->create([
            'weight' => 100,
            'type' => 'cost'  // Lower is better
        ]);

        // Employee 1: Score 60 (higher cost, should get rank 2)
        Evaluation::factory()->create([
            'employee_id' => $employee1->id,
            'criteria_id' => $criteria->id,
            'score' => 60,
            'evaluation_period' => '2024-01'
        ]);

        // Employee 2: Score 40 (lower cost, should get rank 1)
        Evaluation::factory()->create([
            'employee_id' => $employee2->id,
            'criteria_id' => $criteria->id,
            'score' => 40,
            'evaluation_period' => '2024-01'
        ]);

        // Act
        $results = $this->sawService->calculateSAW('2024-01');

        // Assert: Employee 2 should rank higher (lower cost is better)
        $this->assertEquals($employee2->id, $results[0]['employee_id']);
        $this->assertEquals(1, $results[0]['ranking']);
        
        $this->assertEquals($employee1->id, $results[1]['employee_id']);
        $this->assertEquals(2, $results[1]['ranking']);
    }

    /** @test */
    public function it_validates_incomplete_evaluation_data()
    {
        // Arrange: Create incomplete data
        $employee = Employee::factory()->create();
        $criteria1 = Criteria::factory()->create(['weight' => 50]);
        $criteria2 = Criteria::factory()->create(['weight' => 50]);

        // Only create evaluation for one criteria (incomplete)
        Evaluation::factory()->create([
            'employee_id' => $employee->id,
            'criteria_id' => $criteria1->id,
            'score' => 80,
            'evaluation_period' => '2024-01'
        ]);

        // Act & Assert: Should throw exception
        $this->expectException(\Exception::class);
        $this->sawService->calculateSAW('2024-01');
    }

    /** @test */
    public function it_handles_tied_scores_correctly()
    {
        // Arrange: Create employees with same scores
        $employee1 = Employee::factory()->create();
        $employee2 = Employee::factory()->create();
        
        $criteria = Criteria::factory()->create([
            'weight' => 100,
            'type' => 'benefit'
        ]);

        // Both employees get same score
        Evaluation::factory()->create([
            'employee_id' => $employee1->id,
            'criteria_id' => $criteria->id,
            'score' => 90,
            'evaluation_period' => '2024-01'
        ]);

        Evaluation::factory()->create([
            'employee_id' => $employee2->id,
            'criteria_id' => $criteria->id,
            'score' => 90,
            'evaluation_period' => '2024-01'
        ]);

        // Act
        $results = $this->sawService->calculateSAW('2024-01');

        // Assert: Both should have same ranking
        $this->assertEquals(1, $results[0]['ranking']);
        $this->assertEquals(1, $results[1]['ranking']);
        $this->assertEquals($results[0]['total_score'], $results[1]['total_score']);
    }

    /** @test */
    public function it_validates_criteria_weights_total_100_percent()
    {
        // Arrange: Create criteria with invalid total weight
        Employee::factory()->create();
        
        Criteria::factory()->create(['weight' => 60]); // Total = 60% (invalid)
        
        // Act
        $validation = $this->sawService->validateEvaluationPeriod('2024-01');
        
        // Assert
        $this->assertFalse($validation['is_weight_valid']);
        $this->assertEquals(60, $validation['total_weight']);
    }

    /** @test */
    public function it_calculates_multiple_criteria_correctly()
    {
        // Arrange: Real-world scenario with multiple criteria
        $employee = Employee::factory()->create();
        
        $criteria1 = Criteria::factory()->create([
            'name' => 'Performance',
            'weight' => 40,
            'type' => 'benefit'
        ]);
        
        $criteria2 = Criteria::factory()->create([
            'name' => 'Attendance',
            'weight' => 30,
            'type' => 'benefit'
        ]);
        
        $criteria3 = Criteria::factory()->create([
            'name' => 'Errors',
            'weight' => 30,
            'type' => 'cost'
        ]);

        // Perfect scores for benefit criteria, low score for cost criteria
        Evaluation::factory()->create([
            'employee_id' => $employee->id,
            'criteria_id' => $criteria1->id,
            'score' => 100, // Perfect performance
            'evaluation_period' => '2024-01'
        ]);
        
        Evaluation::factory()->create([
            'employee_id' => $employee->id,
            'criteria_id' => $criteria2->id,
            'score' => 95, // Good attendance
            'evaluation_period' => '2024-01'
        ]);
        
        Evaluation::factory()->create([
            'employee_id' => $employee->id,
            'criteria_id' => $criteria3->id,
            'score' => 10, // Few errors (good for cost criteria)
            'evaluation_period' => '2024-01'
        ]);

        // Act
        $results = $this->sawService->calculateSAW('2024-01');

        // Assert
        $this->assertCount(1, $results);
        $this->assertEquals(1, $results[0]['ranking']);
        
        // Score should be weighted combination:
        // Performance: (100/100) * 0.40 = 0.40
        // Attendance: (95/95) * 0.30 = 0.30  
        // Errors: (10/10) * 0.30 = 0.30
        // Total = 1.00
        $this->assertEquals(1.0, $results[0]['total_score']);
    }
}