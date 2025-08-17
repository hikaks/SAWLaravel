<?php

namespace App\Exceptions;

use Exception;

class SAWCalculationException extends Exception
{
    public static function insufficientData(array $validation): self
    {
        $errors = [];
        
        if (!$validation['is_weight_valid']) {
            $errors[] = "Total criteria weight must be 100% (currently: {$validation['total_weight']}%)";
        }
        
        if ($validation['missing_evaluations'] > 0) {
            $errors[] = "Missing {$validation['missing_evaluations']} evaluations from total {$validation['expected_evaluations']} required";
        }
        
        $message = "Cannot calculate SAW: " . implode(', ', $errors);
        
        $exception = new self($message, 422);
        $exception->validation = $validation;
        
        return $exception;
    }
    
    public static function invalidWeights(int $totalWeight): self
    {
        return new self("Invalid criteria weights. Total: {$totalWeight}%, Expected: 100%", 422);
    }
    
    public static function noEmployeesFound(string $period): self
    {
        return new self("No employees found for evaluation period: {$period}", 404);
    }
    
    public static function noCriteriaFound(): self
    {
        return new self("No evaluation criteria found. Please add criteria first.", 404);
    }
    
    public static function divisionByZero(string $context): self
    {
        return new self("Division by zero error in {$context}. Check your data integrity.", 500);
    }
    
    public static function memoryLimit(string $period): self
    {
        return new self("Memory limit exceeded while calculating SAW for period: {$period}. Consider using queue processing.", 500);
    }
    
    public function getValidation(): ?array
    {
        return $this->validation ?? null;
    }
    
    private array $validation = [];
}