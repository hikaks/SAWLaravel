<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'backup:data 
                           {--format=json : Export format (json, sql)}
                           {--tables=* : Specific tables to backup}';

    /**
     * The console command description.
     */
    protected $description = 'Backup evaluation data to storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $format = $this->option('format');
        $tables = $this->option('tables') ?: [
            'employees', 'criterias', 'evaluations', 'evaluation_results'
        ];

        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "backup_saw_data_{$timestamp}.{$format}";

        $this->info("Starting backup process...");
        $this->info("Format: {$format}");
        $this->info("Tables: " . implode(', ', $tables));

        try {
            if ($format === 'json') {
                $this->backupAsJson($tables, $filename);
            } else {
                $this->backupAsSql($tables, $filename);
            }

            $this->info("✅ Backup completed successfully!");
            $this->info("📁 File saved as: storage/app/backups/{$filename}");
            $this->info("📊 You can restore this backup using: php artisan backup:restore {$filename}");

        } catch (\Exception $e) {
            $this->error("❌ Backup failed: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function backupAsJson(array $tables, string $filename): void
    {
        $backup = [
            'created_at' => Carbon::now()->toISOString(),
            'version' => '1.0',
            'tables' => []
        ];

        foreach ($tables as $table) {
            $this->line("Backing up table: {$table}");
            
            $data = DB::table($table)->get();
            $backup['tables'][$table] = [
                'count' => $data->count(),
                'data' => $data->toArray()
            ];
            
            $this->info("  ✓ {$table}: {$data->count()} records");
        }

        // Ensure backup directory exists
        Storage::makeDirectory('backups');
        
        // Save backup file
        Storage::put(
            "backups/{$filename}", 
            json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    private function backupAsSql(array $tables, string $filename): void
    {
        $sql = "-- SAW Evaluation System Backup\n";
        $sql .= "-- Created: " . Carbon::now()->toDateTimeString() . "\n";
        $sql .= "-- Tables: " . implode(', ', $tables) . "\n\n";

        foreach ($tables as $table) {
            $this->line("Backing up table: {$table}");
            
            $sql .= "-- Table: {$table}\n";
            $sql .= "DELETE FROM {$table};\n";
            
            $rows = DB::table($table)->get();
            
            if ($rows->count() > 0) {
                $columns = array_keys((array) $rows->first());
                $sql .= "INSERT INTO {$table} (`" . implode('`, `', $columns) . "`) VALUES\n";
                
                $values = [];
                foreach ($rows as $row) {
                    $rowData = array_map(function($value) {
                        return $value === null ? 'NULL' : "'" . addslashes($value) . "'";
                    }, (array) $row);
                    
                    $values[] = "(" . implode(', ', $rowData) . ")";
                }
                
                $sql .= implode(",\n", $values) . ";\n\n";
            }
            
            $this->info("  ✓ {$table}: {$rows->count()} records");
        }

        // Ensure backup directory exists
        Storage::makeDirectory('backups');
        
        // Save backup file
        Storage::put("backups/{$filename}", $sql);
    }
}