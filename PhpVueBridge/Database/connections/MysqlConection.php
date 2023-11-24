<?php

namespace app\Core\DataBase\Connections;

use app\Core\DataBase\Query\MysqlQueryBuilder;
use app\Core\DataBase\Schema\MysqlBuilder;

class MysqlConection extends Conection
{

    public function getDefaultSchema(): MysqlBuilder
    {
        return $this->schema = new MysqlBuilder($this);
    }

    public function getDefaultQuery(): MysqlQueryBuilder
    {
        return new MysqlQueryBuilder($this);
    }

}
?>