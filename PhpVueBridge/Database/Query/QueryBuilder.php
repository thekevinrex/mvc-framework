<?php

namespace app\Core\DataBase\Query;

use app\Core\DataBase\Connections\Conection;
use app\Core\DataBase\Query\Grammar\QueryGrammar;

class QueryBuilder
{

    protected Conection $conection;

    protected QueryGrammar $grammar;

    public bool $distinct = false;

    public array $columns;

    public string $from;

    public array $conditions;

    public $limit;

    public $order;

    public function __construct(Conection $conection)
    {
        $this->conection = $conection;
        $this->grammar = $this->getDefaultGrammar();
    }

    protected function getDefaultGrammar()
    {
        return new QueryGrammar;
    }

    public function insert(array $insert)
    {

        $keys = array_keys($insert);
        $values = array_values($insert);


        return $this->conection->statement(
            $this->grammar->compileInsert($this, $keys, $values),
        );
    }

    public function get()
    {
        return $this->runSelect();
    }

    public function runSelect()
    {
        return $this->conection->select(
            $this->queryToSql(), $this->prepareBindings()
        );
    }

    protected function queryToSql()
    {
        var_dump(
            $this->grammar->compileSelect(
                $this
            )
        );
        return $this->grammar->compileSelect(
            $this
        );
    }

    protected function prepareBindings()
    {
        return [];
    }

    public function from(string $table)
    {
        $this->from = $table;

        return $this;
    }


    public function select(...$columns)
    {
        if (count($columns) == 0) {
            $columns = ['*'];
        }

        if (!isset($this->columns) || is_null($this->columns)) {
            $this->columns = $columns;
        } else {
            $this->columns = array_merge($this->columns, $columns);
        }

        return $this;
    }

    public function where($columns, $operator = '=', $value = null, $union = 'AND')
    {

        if (is_array($columns)) {
            return $this->whereKeyValue($columns, $operator, $union);
        }

        $column = $columns;
        $type = 'basic';

        $this->conditions[] = compact('type', 'column', 'operator', 'value', 'union');

        return $this;
    }

    protected function whereKeyValue($columns, $operator = '=', $union = 'AND')
    {
        $type = 'basic';

        foreach ($columns as $column => $value) {
            $this->conditions[] = compact('type', 'column', 'operator', 'value', 'union');
        }

        return $this;
    }

    public function orderBy($columns, $order = 'DESC')
    {
        if (is_array($columns)) {
            foreach ($columns as $key => $value) {

                if (is_int($key)) {
                    $key = $value;
                    $value = $order;
                }

                $this->order[] = compact('key', 'value');
            }
        } else {
            $key = $columns;
            $value = $order;

            $this->order[] = compact('key', 'value');
        }

        return $this;
    }

    public function limit($from, $limit = null)
    {
        if (is_null($limit)) {
            $this->limit = $from;
        } else {
            $this->limit = compact('from', 'limit');
        }

        return $this;
    }
}
?>