<?php

namespace app\Core\DataBase\Schema;

use app\Core\DataBase\Connections\Conection;
use app\Core\DataBase\Grammar;

class DataBaseDesign
{

    protected array $columns = [];

    protected array $commands = [];

    protected Conection $conection;

    protected Grammar $grammar;

    protected string $table;

    protected string $charset;

    protected string $collation;

    protected $engine;

    protected static $defaultLength = 255;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function build(Conection $conection, Grammar $grammar)
    {
        $this->conection = $conection;
        $this->grammar = $grammar;

        foreach ($this->generateSql() as $statement) {
            var_dump($statement);
            $conection->statement($statement);
        }
    }

    protected function generateSql(): array
    {
        $statements = [];

        $this->addIndexComands();

        foreach ($this->commands as $comand) {

            $method = 'compile' . ucfirst($comand['comand']);

            if (method_exists($this->grammar, $method)) {
                if (!is_null($statement = $this->grammar->{$method}($this, $comand))) {
                    $statements[] = $statement;
                }
            }
        }

        // var_dump($statements);
        // exit;

        return $statements;
    }

    protected function addIndexComands(): void
    {

        foreach ($this->columns as $column) {
            foreach (['primary', 'unique'] as $index) {

                if (isset($column->{$index}) && $column->{$index} === true) {
                    $this->{$index}($index, $column);
                }

            }
        }
    }


    public function add(): void
    {
        array_unshift($this->commands, $this->createComand('add'));
    }

    public function create(): void
    {
        array_unshift($this->commands, $this->createComand('create'));
    }

    public function change(): void
    {
        array_unshift($this->commands, $this->createComand('change'));
    }


    public function string(string $name, int $length = null)
    {
        $length = $length ?: static::$defaultLength;

        return $this->addColumn('string', $name, compact('length'));
    }

    public function id(string $name = 'id')
    {
        return $this->unsignedInteger($name, true);
    }

    public function integer(string $name, $unsigned = false, $autoIncrement = false)
    {
        return $this->addColumn('integer', $name, compact('unsigned', 'autoIncrement'));
    }

    public function unsignedInteger(string $name, $autoIncrement = false)
    {
        return $this->integer($name, true, $autoIncrement);
    }

    public function charset(string $charset)
    {
        $this->charset = $charset;
    }

    public function collation(string $collation)
    {
        $this->collation = $collation;
    }

    public function engine(string $engine)
    {
        $this->engine = $engine;
    }

    protected function addColumn(string $type, string $name, $params = [])
    {
        $this->columns[] = $column = new DesignColumn($type, $name, $params);

        return $column;
    }

    protected function indexCommand(string $index, $columns, $name = null)
    {
        return $this->addCommand($this->createComand($index, compact('columns', 'name')));
    }

    protected function createComand(string $comand, $params = [])
    {
        return array_merge(compact('comand', $params));
    }

    protected function addCommand($comand)
    {
        $this->commands[] = $comand;

        return $comand;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getEncoding()
    {
        return [
            $this->charset ?? null,
            $this->collation ?? null,
        ];
    }

    public function getEngine()
    {
        return $this->engine;
    }

}

?>