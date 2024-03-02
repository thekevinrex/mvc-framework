<?php 

    namespace app\Core\DataBase\Schema;
    use app\Core\DataBase\Grammar;
    use app\Core\DataBase\Schema\Grammar\MysqlBuilderGrammar;

    class MysqlBuilder extends Builder
    {

        public function getDefaultGrammar() : Grammar
        {
            return new MysqlBuilderGrammar;
        }
        
        public function hasTable(string $table) : bool
        {
            return count(
                $this->conection->select( 
                    $this->grammar->compileTableExist(), 
                    [$this->conection->getDatabase(), $table]
                )
            ) > 0;
        }
    }
?>