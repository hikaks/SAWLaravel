<?php

namespace Database\Seeders;

use App\Models\Criteria;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CriteriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $criterias = [
            [
                'name' => 'Kinerja Kerja',
                'weight' => 25,
                'type' => 'benefit',
            ],
            [
                'name' => 'Kehadiran',
                'weight' => 20,
                'type' => 'benefit',
            ],
            [
                'name' => 'Kemampuan Teknis',
                'weight' => 20,
                'type' => 'benefit',
            ],
            [
                'name' => 'Kerja Sama Tim',
                'weight' => 15,
                'type' => 'benefit',
            ],
            [
                'name' => 'Disiplin',
                'weight' => 10,
                'type' => 'benefit',
            ],
            [
                'name' => 'Komunikasi',
                'weight' => 10,
                'type' => 'benefit',
            ],
        ];

        foreach ($criterias as $criteria) {
            Criteria::create($criteria);
        }

        $this->command->info('6 kriteria default berhasil ditambahkan dengan total bobot 100.');
    }
}
