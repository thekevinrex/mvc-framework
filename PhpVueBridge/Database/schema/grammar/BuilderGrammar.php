<?php

namespace app\Core\DataBase\Schema\Grammar;

use app\Core\DataBase\Grammar;
use app\Core\DataBase\Schema\DataBaseDesign;

class BuilderGrammar extends Grammar
{

    protected $modifiers = [];

    public function getDataBaseName($design)
    {
        return $this->wrapValue(
            ($design instanceof DataBaseDesign)
            ? $design->getTable()
            : $design
        );
    }

    public function getColumns(DataBaseDesign $design)
    {
        $columns = [];

        foreach ($design->getColumns() as $column) {

            $columnsSql = $this->wrapValue($column->getName()) . ' ' . $this->getType($column);
            $columns[] = $this->addModifiers($columnsSql, $column);
        }

        return implode(', ', $columns);
    }

    public function getType($column)
    {
        $method = 'type' . ucfirst($column->getType());
        if (method_exists($this, $method)) {
            return $this->$method($column);
        }

        return $column->getType();
    }

    public function addModifiers($sql, $column)
    {
        foreach ($this->modifiers as $modifier) {
            $method = 'modifier' . ucfirst($modifier);
            if (method_exists($this, $method)) {
                $sql .= $this->$method($column);
            }
        }

        return $sql;
    }

    public function wrapValue($value)
    {
        return $value;
        // TODO fix this

        if ($value !== '*') {
            return '"' . str_replace('"', '""', $value) . '"';
        }

        return $value;
    }
}

?>