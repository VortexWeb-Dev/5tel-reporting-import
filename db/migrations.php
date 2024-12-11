<?php

class Migrations {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function runMigrations() {
        $migrationFiles = glob(__DIR__ . '/migration_files/*.php');
        usort($migrationFiles, function($a, $b) {
            return filemtime($a) - filemtime($b); // Sort migrations by date
        });

        foreach ($migrationFiles as $migrationFile) {
            include $migrationFile;
            echo "Migration: $migrationFile executed successfully!\n";
        }
    }
}
