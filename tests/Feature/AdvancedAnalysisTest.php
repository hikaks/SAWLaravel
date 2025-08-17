<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Models\EvaluationResult;
use App\Services\AdvancedAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class AdvancedAnalysisTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $employees;
    protected $criterias;
    protected $evaluationPeriod;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now()
        ]);

        // Create test data
        $this->setupTestData();
    }

    protected function setupTestData()
    {
        // Create employees
        $this->employees = Employee::factory()->count(5)->create();

        // Create criteria
        $this->criterias = collect([
            Criteria::factory()->create(['name' => 'Performance', 'weight' => 30, 'type' => 'benefit']),
            Criteria::factory()->create(['name' => 'Quality', 'weight' => 25, 'type' => 'benefit']),
            Criteria::factory()->create(['name' => 'Attendance', 'weight' => 20, 'type' => 'benefit']),
            Criteria::factory()->create(['name' => 'Cost', 'weight' => 15, 'type' => 'cost']),
            Criteria::factory()->create(['name' => 'Time', 'weight' => 10, 'type' => 'cost'])
        ]);

        $this->evaluationPeriod = '2024-01';

        // Create evaluations
        foreach ($this->employees as $employee) {
            foreach ($this->criterias as $criteria) {
                Evaluation::factory()->create([
                    'employee_id' => $employee->id,
                    'criteria_id' => $criteria->id,
                    'score' => $this->faker->numberBetween(60, 95),
                    'evaluation_period' => $this->evaluationPeriod
                ]);
            }
        }

        // Create evaluation results
        foreach ($this->employees as $index => $employee) {
            EvaluationResult::factory()->create([
                'employee_id' => $employee->id,
                'total_score' => $this->faker->randomFloat(4, 0.6, 0.95),
                'ranking' => $index + 1,
                'evaluation_period' => $this->evaluationPeriod
            ]);
        }
    }

    /** @test */
    public function user_can_access_analysis_dashboard()
    {
        $response = $this->actingAs($this->user)
            ->get(route('analysis.index'));

        $response->assertStatus(200);
        $response->assertViewIs('analysis.index');
        $response->assertViewHas(['availablePeriods', 'criterias', 'employees']);
    }

    /** @test */
    public function user_can_access_sensitivity_analysis_view()
    {
        $response = $this->actingAs($this->user)
            ->get(route('analysis.sensitivity.view'));

        $response->assertStatus(200);
        $response->assertViewIs('analysis.sensitivity');
        $response->assertViewHas(['availablePeriods', 'criterias', 'selectedPeriod']);
    }

    /** @test */
    public function user_can_run_standard_sensitivity_analysis()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('analysis.sensitivity'), [
                'evaluation_period' => $this->evaluationPeriod
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $responseData = $response->json('data');
        $this->assertArrayHasKey('original_results', $responseData);
        $this->assertArrayHasKey('sensitivity_scenarios', $responseData);
        $this->assertArrayHasKey('summary', $responseData);
    }

    /** @test */
    public function user_can_run_custom_sensitivity_analysis()
    {
        $customWeights = [];
        foreach ($this->criterias as $criteria) {
            $customWeights[] = [
                'criteria_id' => $criteria->id,
                'weight' => $this->faker->numberBetween(10, 40)
            ];
        }

        $response = $this->actingAs($this->user)
            ->postJson(route('analysis.sensitivity'), [
                'evaluation_period' => $this->evaluationPeriod,
                'weight_changes' => $customWeights
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $responseData = $response->json('data');
        $this->assertArrayHasKey('sensitivity_scenarios', $responseData);
        $this->assertArrayHasKey('custom_scenario', $responseData['sensitivity_scenarios']);
    }

    /** @test */
    public function sensitivity_analysis_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('analysis.sensitivity'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['evaluation_period']);
    }

    /** @test */
    public function user_can_run_what_if_analysis()
    {
        $scenarios = [
            [
                'name' => 'Scenario 1',
                'type' => 'weight_changes',
                'changes' => [
                    $this->criterias->first()->id => 40,
                    $this->criterias->last()->id => 20
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('analysis.what-if'), [
                'evaluation_period' => $this->evaluationPeriod,
                'scenarios' => $scenarios
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function user_can_run_multi_period_comparison()
    {
        // Create data for another period
        $secondPeriod = '2024-02';
        foreach ($this->employees as $employee) {
            foreach ($this->criterias as $criteria) {
                Evaluation::factory()->create([
                    'employee_id' => $employee->id,
                    'criteria_id' => $criteria->id,
                    'score' => $this->faker->numberBetween(60, 95),
                    'evaluation_period' => $secondPeriod
                ]);
            }
        }

        foreach ($this->employees as $index => $employee) {
            EvaluationResult::factory()->create([
                'employee_id' => $employee->id,
                'total_score' => $this->faker->randomFloat(4, 0.6, 0.95),
                'ranking' => $this->faker->numberBetween(1, 5),
                'evaluation_period' => $secondPeriod
            ]);
        }

        $response = $this->actingAs($this->user)
            ->postJson(route('analysis.comparison'), [
                'periods' => [$this->evaluationPeriod, $secondPeriod]
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $responseData = $response->json('data');
        $this->assertArrayHasKey($this->evaluationPeriod, $responseData);
        $this->assertArrayHasKey($secondPeriod, $responseData);
    }

    /** @test */
    public function user_can_run_performance_forecast()
    {
        // Create historical data for forecasting
        $periods = ['2023-01', '2023-02', '2023-03'];
        $employee = $this->employees->first();

        foreach ($periods as $index => $period) {
            EvaluationResult::factory()->create([
                'employee_id' => $employee->id,
                'total_score' => 0.7 + ($index * 0.05), // Trending upward
                'ranking' => 3 - $index, // Improving rank
                'evaluation_period' => $period
            ]);
        }

        $response = $this->actingAs($this->user)
            ->postJson(route('analysis.forecast'), [
                'employee_id' => $employee->id,
                'periods_ahead' => 3
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $responseData = $response->json('data');
        $this->assertArrayHasKey('historical_data', $responseData);
        $this->assertArrayHasKey('forecasts', $responseData);
        $this->assertArrayHasKey('linear_trend', $responseData['forecasts']);
        $this->assertArrayHasKey('moving_average', $responseData['forecasts']);
        $this->assertArrayHasKey('weighted_average', $responseData['forecasts']);
    }

    /** @test */
    public function forecast_requires_minimum_historical_data()
    {
        $employee = $this->employees->first();

        $response = $this->actingAs($this->user)
            ->postJson(route('analysis.forecast'), [
                'employee_id' => $employee->id,
                'periods_ahead' => 3
            ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false
        ]);
    }

    /** @test */
    public function user_can_run_advanced_statistics()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('analysis.statistics'), [
                'periods' => [$this->evaluationPeriod]
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function user_can_get_available_periods()
    {
        $response = $this->actingAs($this->user)
            ->get(route('analysis.periods'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $responseData = $response->json('data');
        $this->assertContains($this->evaluationPeriod, $responseData);
    }

    /** @test */
    public function user_can_get_criterias()
    {
        $response = $this->actingAs($this->user)
            ->get(route('analysis.criterias'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $responseData = $response->json('data');
        $this->assertCount(5, $responseData);
    }

    /** @test */
    public function unauthorized_user_cannot_access_analysis()
    {
        $response = $this->get(route('analysis.index'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function analysis_endpoints_require_authentication()
    {
        $endpoints = [
            ['POST', route('analysis.sensitivity')],
            ['POST', route('analysis.what-if')],
            ['POST', route('analysis.comparison')],
            ['POST', route('analysis.forecast')],
            ['POST', route('analysis.statistics')],
        ];

        foreach ($endpoints as [$method, $url]) {
            $response = $this->json($method, $url);
            $response->assertStatus(401);
        }
    }

    /** @test */
    public function multi_period_comparison_validates_minimum_periods()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('analysis.comparison'), [
                'periods' => [$this->evaluationPeriod] // Only one period
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['periods']);
    }

    /** @test */
    public function multi_period_comparison_validates_maximum_periods()
    {
        $periods = [];
        for ($i = 1; $i <= 7; $i++) {
            $periods[] = "2024-{$i:02d}";
        }

        $response = $this->actingAs($this->user)
            ->postJson(route('analysis.comparison'), [
                'periods' => $periods // Too many periods
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['periods']);
    }

    /** @test */
    public function what_if_analysis_validates_scenario_structure()
    {
        $invalidScenarios = [
            [
                'name' => 'Invalid Scenario',
                'type' => 'invalid_type', // Invalid type
                'changes' => []
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('analysis.what-if'), [
                'evaluation_period' => $this->evaluationPeriod,
                'scenarios' => $invalidScenarios
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['scenarios.0.type']);
    }

    /** @test */
    public function performance_forecast_validates_employee_exists()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('analysis.forecast'), [
                'employee_id' => 99999, // Non-existent employee
                'periods_ahead' => 3
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['employee_id']);
    }

    /** @test */
    public function sensitivity_analysis_handles_invalid_evaluation_period()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('analysis.sensitivity'), [
                'evaluation_period' => '2099-12' // Period with no data
            ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false
        ]);
    }
}