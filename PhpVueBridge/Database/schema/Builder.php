<?php

namespace app\Core\DataBase\Schema;

use app\Core\DataBase\Connections\Conection;
use app\Core\DataBase\Grammar;
use app\Core\DataBase\Schema\Grammar\BuilderGrammar;

abstract class Builder
{

    protected Conection $conection;

    protected Grammar $grammar;

    public function __construct(Conection $conection)
    {
        $this->conection = $conection;

        $this->grammar = $this->getDefaultGrammar();
    }

    public function getDefaultGrammar(): Grammar
    {
        return new BuilderGrammar;
    }


    abstract function hasTable(string $table): bool;

    public function create(string $table, \Closure $callback): void
    {
        $this->buildDesign(function () use ($table, $callback) {

            $databaseDesign = $this->createDataBaseDesign($table);

            $databaseDesign->create();

            $callback($databaseDesign);

            return $databaseDesign;
        });
    }

    protected function createDataBaseDesign(string $table)
    {
        $design = new DataBaseDesign($table);

        if (array_key_exists('charset', $this->conection->getConfig())) {
            $design->charset($this->conection->getConfig()['charset']);
        }

        if (array_key_exists('collation', $this->conection->getConfig())) {
            $design->collation($this->conection->getConfig()['collation']);
        }

        if (array_key_exists('engine', $this->conection->getConfig())) {
            $design->engine($this->conection->getConfig()['engine']);
        }

        return $design;
    }

    protected function buildDesign($design): void
    {

        if ($design instanceof \Closure) {
            $design = $design();
        }

        $design->build($this->conection, $this->grammar);

    }
}
?>