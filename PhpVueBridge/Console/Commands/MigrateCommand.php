<?php

namespace app\Core\Console\Commands;

use app\Core\Application;
use app\Core\Console\Command;
use app\Core\Console\Input;
use app\Core\DataBase\Migrator\Migrator;

class MigrateCommand extends Command
{

    protected Migrator $migrator;

    public function __construct(Migrator $migrator, Application $app, Input $input)
    {
        parent::__construct($app, $input);

        $this->migrator = $migrator;
    }

    public function excecute()
    {
        $this->migrator->useConection(
            $this->app->resolve('db')->connection()
        );

        $this->prepareDatabase();

        $this->migrator->run(
            $this->getMigrationsPath()
        );
    }

    protected function prepareDatabase(): void
    {
        if (!$this->migrator->tableExist()) {
            $this->migrator->createTable();
        }

        echo 'table';
    }

    protected function getMigrationsPath()
    {
        return [
            $this->app->getDataBasePath() . '/migrations'
        ];
    }
}
?>