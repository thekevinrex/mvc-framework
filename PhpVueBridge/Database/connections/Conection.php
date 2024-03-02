<?php

namespace app\Core\DataBase\Connections;

use app\Core\DataBase\Query\QueryBuilder;
use app\Core\DataBase\Schema\Builder;

abstract class Conection
{

    protected $pdo;

    protected string $database;

    protected array $config;

    protected Builder $schema;

    public function __construct($pdo, $database, $config)
    {
        $this->pdo = $pdo;
        $this->database = $database;
        $this->config = $config;
    }

    public function getDefaultSchema(): Builder
    {
        throw new \Exception('not defined schema builder');
        // TODO correct exception
    }

    public function getDefaultQuery(): QueryBuilder
    {
        throw new \Exception('not defined querry builder');
    }

    public function getSchema()
    {
        return $this->schema ?? $this->getDefaultSchema();
    }

    public function getQuery(): QueryBuilder
    {
        return $this->getDefaultQuery();
    }

    public function query()
    {
        return $this->getQuery();
    }

    public function table(string $table)
    {
        return $this->getQuery()->from($table);
    }

    public function select($query, $bindings = [])
    {
        return $this->runQuery(function ($query, $bindings) {

            $statement = $this->getSelectPDO()->prepare($query);

            $this->bindValues($statement, $bindings);

            $statement->execute();

            return $statement->fetchAll();

        }, $query, $bindings);
    }

    public function statement($query, $bindings = [])
    {
        return $this->runQuery(function ($query, $bindings) {

            $statement = $this->getPDO()->prepare($query);

            $this->bindValues($statement, $bindings);

            return $statement->execute();

        }, $query, $bindings);
    }

    protected function runQuery(\Closure $callback, $query, $bindings = [])
    {

        $start = microtime(true);

        try {
            $result = $callback($query, $this->prepareBindings($bindings));
        } catch (\Throwable $e) {
            throw $e;
        }

        // TODO calc query time

        return $result;
    }

    public function bindValues($statement, $bindings)
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                match (true) {
                    is_int($value) => \PDO::PARAM_INT,
                    is_resource($value) => \PDO::PARAM_LOB,
                    default => \PDO::PARAM_STR
                },
            );
        }
    }

    protected function prepareBindings(array $bindings)
    {
        return $bindings;
    }

    protected function getSelectPDO()
    {
        return $this->getPDO();
    }

    protected function getPDO()
    {
        if ($this->pdo instanceof \Closure) {
            return $this->pdo = call_user_func($this->pdo);
        }

        return $this->pdo;
    }

    public function getDatabase()
    {
        return $this->database;
    }

    public function getConfig()
    {
        return $this->config;
    }
}
?>