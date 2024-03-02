<?php


namespace app\Core\DataBase\Query\Grammar;

use app\Core\DataBase\Query\QueryBuilder;

class QueryGrammar
{

    protected $queryComponent = [
        'columns',
        'from',
        'conditions',
        'order',
        'limit'
    ];

    public function compileSelect(QueryBuilder $query)
    {


        if (!isset($query->columns) || empty($query->columns)) {
            $query->columns = ['*'];
        }


        $sql = implode(
            ' ',
            $this->compileComponents($query)
        );

        return $sql;
    }

    public function compileComponents(QueryBuilder $query)
    {
        $sql = [];

        foreach ($this->queryComponent as $component) {
            if (isset($query->{$component}) && !is_null($query->{$component})) {
                $method = 'compile' . ucfirst($component);
                if (method_exists($this, $method)) {
                    $sql[] = $this->$method($query);
                }
            }
        }

        return $sql;
    }

    public function compileFrom(QueryBuilder $query)
    {
        return 'from ' . $query->from;
    }

    public function compileConditions(QueryBuilder $query)
    {
        if (is_null($query->conditions) || empty($query->conditions)) {
            return '';
        }

        $conditions = [];

        foreach ($query->conditions as $condition) {
            $method = 'where' . ucfirst($condition['type']);
            if (method_exists($this, $method)) {
                $conditions[] = strtoupper($condition['union']) . ' ' . $this->$method($condition);
            }
        }

        $sql = implode(' ', $conditions);

        if (str_starts_with($sql, 'AND')) {
            $sql = substr($sql, 4);
        }

        if (str_starts_with($sql, 'OR')) {
            $sql = substr($sql, 3);
        }

        return 'where ' . $sql;
    }

    public function compileOrder(QueryBuilder $query)
    {
        $order = array_map(function ($order) use ($query) {
            return $order['key'] . ' ' . strtoupper($order['value']);
        }, $query->order);

        return 'order by ' . implode(', ', $order);
    }

    public function compileLimit(QueryBuilder $query)
    {
        if (is_array($query->limit)) {
            return "limit {$query->limit['from']},{$query->limit['limit']}";
        } else {
            return 'limit ' . $query->limit;
        }
    }


    // wheres

    public function whereBasic(array $condition)
    {
        return $condition['column'] . ' ' . $condition['operator'] . ' ' . "'{$condition['value']}'";
    }
}

?>