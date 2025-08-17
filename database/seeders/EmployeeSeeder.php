<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = [
            [
                'employee_code' => 'EMP001',
                'name' => 'Ahmad Santoso',
                'position' => 'Senior Developer',
                'department' => 'IT Development',
                'email' => 'ahmad.santoso@company.com',
            ],
            [
                'employee_code' => 'EMP002',
                'name' => 'Siti Nurhaliza',
                'position' => 'UI/UX Designer',
                'department' => 'Design',
                'email' => 'siti.nurhaliza@company.com',
            ],
            [
                'employee_code' => 'EMP003',
                'name' => 'Budi Prasetyo',
                'position' => 'Project Manager',
                'department' => 'Project Management',
                'email' => 'budi.prasetyo@company.com',
            ],
            [
                'employee_code' => 'EMP004',
                'name' => 'Maya Indira',
                'position' => 'Business Analyst',
                'department' => 'Business Development',
                'email' => 'maya.indira@company.com',
            ],
            [
                'employee_code' => 'EMP005',
                'name' => 'Joko Widodo',
                'position' => 'DevOps Engineer',
                'department' => 'IT Infrastructure',
                'email' => 'joko.widodo@company.com',
            ],
            [
                'employee_code' => 'EMP006',
                'name' => 'Lisa Maharani',
                'position' => 'Quality Assurance',
                'department' => 'QA Testing',
                'email' => 'lisa.maharani@company.com',
            ],
            [
                'employee_code' => 'EMP007',
                'name' => 'Rian Pratama',
                'position' => 'Junior Developer',
                'department' => 'IT Development',
                'email' => 'rian.pratama@company.com',
            ],
            [
                'employee_code' => 'EMP008',
                'name' => 'Devi Safitri',
                'position' => 'HR Specialist',
                'department' => 'Human Resources',
                'email' => 'devi.safitri@company.com',
            ],
            [
                'employee_code' => 'EMP009',
                'name' => 'Eko Prasetio',
                'position' => 'Marketing Manager',
                'department' => 'Marketing',
                'email' => 'eko.prasetio@company.com',
            ],
            [
                'employee_code' => 'EMP010',
                'name' => 'Rina Kurniawan',
                'position' => 'Finance Analyst',
                'department' => 'Finance',
                'email' => 'rina.kurniawan@company.com',
            ],
        ];

        foreach ($employees as $employee) {
            Employee::create($employee);
        }

        $this->command->info('10 data karyawan sample berhasil ditambahkan.');
    }
}
