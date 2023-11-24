<?php

namespace app\Core\DataBase\Migrator;

use app\Core\DataBase\Connections\Conection;
use app\Core\DataBase\Schema\DataBaseDesign;

class migrationTable
{

    protected Conection $conection;

    protected string $table = 'migrations';


    public function setConection(Conection $conection): void
    {
        $this->conection = $conection;
    }

    public function registerMigration(array $migration)
    {
        $this->table()->insert($migration);
    }

    public function getRunnedMigrations()
    {
        return array_map(function ($migration) {
            return $migration['migration'];
        }, $this->table()->get() ?: []);
    }

    public function getNewMigrationsBatch()
    {
        return $this->getLastMigrationsBatch() + 1;
    }

    public function getLastMigrationsBatch()
    {
        return (int) $this->table()
            ->select('batch')
            ->orderBy('batch')
            ->limit(1)
            ->get() ?: 0;
    }

    protected function table()
    {
        return $this->conection->table($this->table);
    }

    public function tableExist()
    {
        return $this->conection->getSchema()->hasTable($this->table);
    }

    public function createTable(): void
    {
        $this->conection->getSchema()->create($this->table, function (DataBaseDesign $design) {
            $design->id();
            $design->string('migration');
            $design->integer('batch');
        });
    }

}
?>