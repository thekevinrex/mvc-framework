<?php

namespace app\Core\DataBase\Schema\Grammar;

use app\Core\DataBase\Schema\DataBaseDesign;
use app\Core\DataBase\Schema\DesignColumn;

class MysqlBuilderGrammar extends BuilderGrammar
{

    protected $modifiers = ['Unsigned', 'Nullable', 'Default', 'Increment'];

    public function compileTableExist()
    {
        return "select * from information_schema.tables where table_schema = ? and table_name = ? and table_type = 'BASE TABLE'";
    }

    public function compileCreate(DataBaseDesign $design, array $comand)
    {
        $sql = $this->compileCreateTable($design);

        $sql = $this->compileCreateEncoding($sql, $design);

        return $this->compileCreateEngine($sql, $design);
    }

    public function compileCreateTable(DataBaseDesign $design)
    {
        return sprintf(
            'create table %s (%s)',
            $this->getDataBaseName($design),
            $this->getColumns($design),
        );
    }

    public function compileCreateEncoding($sql, DataBaseDesign $design)
    {

        [$charset, $collation] = $design->getEncoding();

        if (!is_null($charset)) {
            $sql .= ' default character set ' . $charset;
        }

        if (!is_null($collation)) {
            $sql .= " collate '{$collation}'";
        }

        return $sql;
    }

    public function compileCreateEngine($sql, DataBaseDesign $design)
    {
        if (!is_null($engine = $design->getEngine())) {
            $sql .= ' engine = ' . $engine;
        }

        return $sql;
    }

    // Types ()

    public function typeString(DesignColumn $column)
    {
        return "varchar({$column->getLength()})";
    }

    public function typeInteger(DesignColumn $column)
    {
        return 'int';
    }


    // Modifiers ()

    public function modifierUnsigned(DesignColumn $column)
    {
        if ($column->isUnsigned()) {
            return ' unsigned';
        }
    }

    public function modifierNullable(DesignColumn $column)
    {
        return $column->isNullable() ? ' null' : ' not null';
    }

    public function modifierDefault(DesignColumn $column)
    {
        if (!is_null($column->getDefault())) {
            return ' default ' . "'{$column->getDefault()}'";
        }
    }

    public function modifierIncrement(DesignColumn $column)
    {
        if (in_array($column->getType(), ['integer', 'biginteger']) && $column->isAutoIncrement()) {
            return ' auto_increment primary key';
        }
    }
}