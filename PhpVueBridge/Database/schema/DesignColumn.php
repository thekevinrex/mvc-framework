<?php

namespace app\Core\DataBase\Schema;

class DesignColumn
{

    protected string $type;

    protected string $name;

    protected $length;

    protected $default;

    protected $autoIncrement = false;

    protected $nullable = false;

    protected $unsigned = false;

    protected $params = [];

    public function __construct(string $type, string $name, array $params = [])
    {
        $this->type = $type;
        $this->name = $name;


        foreach ($params as $param => $value) {
            if (property_exists($this, $param)) {
                $this->{$param} = $value;
            }
        }

        $this->params = $params;
    }

    public function length(int $length)
    {
        $this->length = $length;

        return $this;
    }

    public function default($default)
    {
        $this->default = $default;

        return $this;
    }

    public function autoIncrement(bool $increment)
    {
        $this->autoIncrement = $increment;

        return $this;
    }

    // getters ()

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getLength()
    {
        return $this->length;
    }

    public function isUnsigned()
    {
        return $this->unsigned;
    }

    public function isNullable()
    {
        return $this->nullable;
    }

    public function isAutoIncrement()
    {
        return $this->autoIncrement;
    }

    public function getDefault()
    {
        return $this->default;
    }
}
?>