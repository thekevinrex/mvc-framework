<?php


namespace PhpVueBridge\View;

use PhpVueBridge\Bedrock\Interfaces\Htmleable;

class ComponentSlot implements Htmleable
{

    public ComponentAttributeBag $attributes;

    protected $name;

    protected $content;

    public function __construct($attributes)
    {
        $this->attributes = new ComponentAttributeBag($attributes);
    }

    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function toHtml(): string
    {
        return $this->content;
    }

    public function __toString()
    {
        return $this->toHtml();
    }
}
