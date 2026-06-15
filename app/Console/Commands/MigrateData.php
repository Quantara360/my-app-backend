<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting migration from SQLite to MySQL...");

        try {
            // Set sqlite database path explicitly since DB_DATABASE is now 'myfirstproject'
            config(['database.connections.sqlite.database' => database_path('database.sqlite')]);

            // Disable foreign keys on MySQL
            \Illuminate\Support\Facades\DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0;');

            // Get all tables from SQLite
            $tables = \Illuminate\Support\Facades\DB::connection('sqlite')->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

            foreach ($tables as $tableInfo) {
                $tableName = $tableInfo->name;
                
                $this->info("Copying table: {$tableName}...");
                
                $rows = \Illuminate\Support\Facades\DB::connection('sqlite')->table($tableName)->get();
                $insertData = array_map(function ($row) {
                    return (array) $row;
                }, $rows->toArray());
                
                if (count($insertData) > 0) {
                    \Illuminate\Support\Facades\DB::connection('mysql')->table($tableName)->truncate(); // Ensure table is empty
                    $chunks = array_chunk($insertData, 100);
                    foreach ($chunks as $chunk) {
                        \Illuminate\Support\Facades\DB::connection('mysql')->table($tableName)->insert($chunk);
                    }
                    $this->info("Copied " . count($insertData) . " rows.");
                } else {
                    $this->info("Table {$tableName} is empty.");
                }
            }

            // Enable foreign keys
            \Illuminate\Support\Facades\DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info("Migration successfully complete!");
        } catch (\Exception $e) {
            $this->error("Error during migration: " . $e->getMessage());
            \Illuminate\Support\Facades\DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}
