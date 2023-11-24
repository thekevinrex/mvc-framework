<?php

namespace app\Core\DataBase\Migrator;

use app\Core\DataBase\Connections\Conection;

class Migrator
{

    protected migrationTable $migrationTable;

    protected Conection $conection;

    protected $paths;

    public function __construct(MigrationTable $migrationTable)
    {
        $this->migrationTable = $migrationTable;
    }

    public function run(array $migrationPath = [])
    {

        $this->paths = $migrationPath;
        $migrationsFiles = $this->getMigrationsFiles();

        $runnedMigrations = $this->migrationTable->getRunnedMigrations();

        $runMigrations = array_filter($migrationsFiles, function ($path) use ($runnedMigrations) {
            return !in_array($this->cleanMigrationPath($path), $runnedMigrations);
        });

        $this->runMigrations($runMigrations);

        return $runMigrations;
    }

    protected function runMigrations(array $migrations)
    {

        $batch = $this->migrationTable->getNewMigrationsBatch();

        foreach ($migrations as $path) {

            $migration = $this->resolveMigration($path);

            if ($this->runUp($migration, $path)) {
                $this->migrationTable->registerMigration([
                    'migration' => $this->cleanMigrationPath($path),
                    'batch' => $batch,
                ]);
            }
        }
    }

    public function runUp($migration, $path)
    {
        if (!is_object($migration)) {
            $migration = $this->resolveMigration($migration);
        }

        if (!method_exists($migration, 'up')) {
            return false;
        }

        $migration->up();

        return true;
    }

    protected function resolveMigration(string $path)
    {
        $migration = require_once $path;

        if (is_object($migration)) {
            return $migration;
        }

        return new($this->getMigrationClassName($path));
    }

    protected function getMigrationClassName($path)
    {
        $migrationData = $this->resolveMigrationData($path);

        return $migrationData['class'];
    }

    protected function resolveMigrationData(string $path)
    {
        $data = [];

        $path = $this->cleanMigrationPath($path);

        $pices = explode('_', $path, 5);

        if (count($pices) < 5) {
            return ['name' => $path];
        }

        $data = [
            'year' => $pices[0] ?? date('y'),
            'month' => $pices[1] ?? date('m'),
            'day' => $pices[2] ?? date('d'),
            'number' => (int) $pices[3] ?? 1,
            'name' => $pices[4] ?? $path,
        ];

        $namesParts = explode('_', $pices[4] ?? $path, 3);

        $command = $namesParts[0] ?? 'create';
        $objective = $namesParts[1] ?? 'table';
        $name = $namesParts[2] ?? $data['name'];

        $className = str_ends_with($name, '.php')
            ? substr($name, 0, -4)
            : $name;


        $data['migration'] = compact('command', 'objective');
        $data['class'] = ucfirst($className);

        return $data;
    }

    protected function cleanMigrationPath(string $migration)
    {
        $migration = str_replace('.php', '', basename($migration));

        return str_starts_with($migration, '/') ? substr($migration, 1) : $migration;
    }

    protected function getMigrationsFiles()
    {
        $migrations = [];

        foreach ($this->paths as $path) {
            $files = scandir($path);
            foreach ($files as $file) {
                if (preg_match('/\.(?:php)$/', $file)) {
                    $migrations[] = $path . '/' . $file;
                }
            }
        }

        return $migrations;
    }

    public function useConection(Conection $conection): void
    {
        $this->conection = $conection;

        $this->migrationTable->setConection($conection);
    }

    public function tableExist()
    {
        return $this->migrationTable->tableExist();
    }

    public function createTable(): void
    {
        $this->migrationTable->createTable();
    }
}
?>