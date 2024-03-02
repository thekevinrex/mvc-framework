<?php


namespace PhpVueBridge\View\Engines;

use PhpVueBridge\View\Managers\ViewManager;
use PhpVueBridge\View\Compilers\CompilerFactory;

class CompilerEngine extends PhPEngine
{

    protected array $compiling = [];

    protected array $compiledFiles = [];

    public function __construct(
        protected CompilerFactory $compiler
    ) {
    }

    public function get(string $path, array $data = []): string
    {
        $this->compiling[] = $path;

        if (!$this->verifyIsCompiled($path)) {
            $this->compiler->compile($path);
        }

        try {
            $content = $this->getFileContent($this->compiler->getCompiledPath($path), $data);
        } catch (\Exception $e) {
            throw $e;
        }

        $this->compiledFiles[$path] = true;

        array_pop($this->compiling);

        return $content;
    }

    protected function verifyIsCompiled($path)
    {
        if (!isset($this->compiledFiles[$path]) && $this->compiler->shouldCompile($path)) {
            return false;
        }

        return true;
    }

    public function render($path, array $data = []): string
    {
        return $this->get($path, $data);
    }
}
