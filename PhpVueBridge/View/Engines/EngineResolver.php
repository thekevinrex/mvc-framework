<?php

namespace PhpVueBridge\View\Engines;

class EngineResolver {

    protected array $engines = [];

    protected array $resolved = [];

    public function __construct(

    ) {

    }

    public function register(string $engine, \Closure $resolver): void {
        $this->engines[$engine] = $resolver;
    }

    public function resolve(string $engine) {

        if(isset($this->resolved[$engine])) {
            return $this->resolved[$engine];
        }

        if(!isset($this->engines[$engine])) {
            throw new \InvalidArgumentException(sprintf('Engine "%s" does not exist.', $engine));
        }

        $this->resolved[$engine] = $resolvedEngine = call_user_func($this->engines[$engine]);

        return $resolvedEngine;
    }
}
?>