<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed authentication users first
        $this->call([
            AdminUserSeeder::class,
        ]);

        // Seed data SAW
        $this->call([
            CriteriaSeeder::class,
            EmployeeSeeder::class,
            EvaluationSeeder::class,
        ]);
    }
}
