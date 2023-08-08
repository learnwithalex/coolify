<?php

namespace Database\Seeders;

use App\Models\StandaloneDocker;
use App\Models\StandalonePostgresql;
use Illuminate\Database\Seeder;

class StandalonePostgresqlSeeder extends Seeder
{
    public function run(): void
    {
        StandalonePostgresql::create([
            'name' => 'Local PostgreSQL',
            'description' => 'Local PostgreSQL for testing',
            'postgres_password' => 'postgres',
            'environment_id' => 1,
            'destination_id' => 1,
            'destination_type' => StandaloneDocker::class,
            'init_scripts' => [
                [
                    'index' => 0,
                    'filename' => 'init_test_db.sql',
                    'content' => 'CREATE DATABASE test;'
                ]
            ]
        ]);
    }
}