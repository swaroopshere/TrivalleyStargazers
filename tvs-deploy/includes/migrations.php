<?php
/**
 * TVS Database Migration System
 *
 * Tracks and runs database migrations in order.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

class Migrations {
    private $migrationsPath;
    private $pdo;

    public function __construct() {
        $this->migrationsPath = ROOT_PATH . '/data/migrations';
        $this->pdo = db()->getConnection();
        $this->ensureMigrationsTable();
    }

    /**
     * Create migrations tracking table if it doesn't exist
     */
    private function ensureMigrationsTable(): void {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                batch INT NOT NULL,
                executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    /**
     * Get list of all migration files
     */
    public function getMigrationFiles(): array {
        $files = glob($this->migrationsPath . '/*.php');
        $migrations = [];

        foreach ($files as $file) {
            $filename = basename($file);
            // Only include files matching pattern: XXX_name.php (e.g., 001_initial.php)
            if (preg_match('/^(\d{3})_(.+)\.php$/', $filename, $matches)) {
                $migrations[$matches[1]] = [
                    'version' => $matches[1],
                    'name' => $matches[2],
                    'filename' => $filename,
                    'path' => $file
                ];
            }
        }

        ksort($migrations);
        return $migrations;
    }

    /**
     * Get list of migrations that have been run
     */
    public function getExecutedMigrations(): array {
        $result = $this->pdo->query("SELECT migration, batch, executed_at FROM migrations ORDER BY migration");
        $executed = [];
        foreach ($result as $row) {
            $executed[$row['migration']] = $row;
        }
        return $executed;
    }

    /**
     * Get pending migrations (not yet run)
     */
    public function getPendingMigrations(): array {
        $all = $this->getMigrationFiles();
        $executed = $this->getExecutedMigrations();

        $pending = [];
        foreach ($all as $version => $migration) {
            if (!isset($executed[$migration['filename']])) {
                $pending[$version] = $migration;
            }
        }

        return $pending;
    }

    /**
     * Get current batch number
     */
    private function getCurrentBatch(): int {
        $result = $this->pdo->query("SELECT MAX(batch) as batch FROM migrations");
        $row = $result->fetch();
        return ($row['batch'] ?? 0) + 1;
    }

    /**
     * Run a single migration
     */
    public function runMigration(array $migration): array {
        $results = ['success' => true, 'messages' => []];

        try {
            // Load the migration file
            $migrationClass = require $migration['path'];

            if (!is_object($migrationClass) || !method_exists($migrationClass, 'up')) {
                throw new Exception("Migration must return an object with an 'up' method");
            }

            // Run the migration
            $this->pdo->beginTransaction();

            $migrationResults = $migrationClass->up($this->pdo);
            if (is_array($migrationResults)) {
                $results['messages'] = $migrationResults;
            }

            // Record the migration
            $batch = $this->getCurrentBatch();
            $stmt = $this->pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
            $stmt->execute([$migration['filename'], $batch]);

            $this->pdo->commit();

            $results['messages'][] = "Migration {$migration['filename']} completed successfully";

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $results['success'] = false;
            $results['messages'][] = "Error in {$migration['filename']}: " . $e->getMessage();
        }

        return $results;
    }

    /**
     * Run all pending migrations
     */
    public function runAll(): array {
        $pending = $this->getPendingMigrations();
        $results = [
            'success' => true,
            'run' => 0,
            'messages' => []
        ];

        if (empty($pending)) {
            $results['messages'][] = "No pending migrations to run.";
            return $results;
        }

        foreach ($pending as $migration) {
            $migrationResult = $this->runMigration($migration);
            $results['messages'] = array_merge($results['messages'], $migrationResult['messages']);

            if ($migrationResult['success']) {
                $results['run']++;
            } else {
                $results['success'] = false;
                break; // Stop on first failure
            }
        }

        return $results;
    }

    /**
     * Get migration status for display
     */
    public function getStatus(): array {
        $all = $this->getMigrationFiles();
        $executed = $this->getExecutedMigrations();

        $status = [];
        foreach ($all as $version => $migration) {
            $isExecuted = isset($executed[$migration['filename']]);
            $status[] = [
                'version' => $migration['version'],
                'name' => $migration['name'],
                'filename' => $migration['filename'],
                'executed' => $isExecuted,
                'executed_at' => $isExecuted ? $executed[$migration['filename']]['executed_at'] : null,
                'batch' => $isExecuted ? $executed[$migration['filename']]['batch'] : null
            ];
        }

        return $status;
    }
}

/**
 * Helper function to get Migrations instance
 */
function migrations(): Migrations {
    static $instance = null;
    if ($instance === null) {
        $instance = new Migrations();
    }
    return $instance;
}
