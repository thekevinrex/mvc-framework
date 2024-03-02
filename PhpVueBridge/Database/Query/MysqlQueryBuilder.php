<?php

namespace app\Core\DataBase\Query;

use app\Core\DataBase\Query\Grammar\MysqlQueryGrammar;

class MysqlQueryBuilder extends QueryBuilder
{

    protected function getDefaultGrammar()
    {
        return new MysqlQueryGrammar;
    }
}
?>