<?php
namespace app\Core\DataBase\Query\Grammar;

use app\Core\DataBase\Query\QueryBuilder;

class MysqlQueryGrammar extends QueryGrammar
{

    public function compileColumns(QueryBuilder $query)
    {

        if ($query->distinct) {
            $sql = 'select distinct ';
        } else {
            $sql = 'select';
        }

        return $sql . ' ' . $this->columnize($query->columns);
    }

    public function columnize(array $columns)
    {
        if (count($columns) == 1 && $columns[0] == '*') {
            return '*';
        } else {
            return implode(',', $columns);
        }
    }

    public function compileInsert(QueryBuilder $query, $keys, $values)
    {
        if (empty($keys)) {
            return 'insert into ' . $query->from . " default values ({$this->columnizeValues($values)})";
        } else {
            return 'insert into ' . $query->from . " ({$this->columnizeKeys($keys)}) values ({$this->columnizeValues($values)})";
        }
    }

    public function columnizeKeys(array $keys)
    {
        return implode(', ', $keys);
    }

    public function columnizeValues(array $values)
    {
        return implode(', ', array_map(function ($value) {
            return "'$value'";
        }, $values));
    }
}
?>