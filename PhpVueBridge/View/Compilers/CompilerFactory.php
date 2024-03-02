<?php

namespace PhpVueBridge\View\Compilers;

use PhpVueBridge\Bedrock\Application;
use PhpVueBridge\View\Compilers\Exceptions\CompilerException;

class CompilerFactory
{

    protected array $extensions = [];

    protected array $directives = [];

    protected array $compilers = [];

    protected array $resolvedCompilers = [];

    protected array $precompilers = [];

    public function __construct(
        protected string $basePath,
        protected string $compiledPath,
        protected string $compiledExtension,
        protected bool $cacheCompiled = false
    ) {
    }

    public function compile(string $path, array $data = [])
    {
        $compiler = $this->resolveCompiler(
            $compilerName = $this->getCompilerFromPath($path)
        );

        return $compiler->make($path);
    }

    /**
     * 
     * Get the compiled path for the given view
     */
    public function getCompiledPath($file)
    {
        return $this->compiledPath
            . '/' . $this->getCompiledName($file)
            . '.' . $this->compiledExtension;
    }

    /**
     * Encode the given view name
     */
    protected function getCompiledName(string $file)
    {
        return sha1('compiled_view_' . str_replace($this->basePath, '', $file));
    }

    /**
     * If the given view should be compiled
     */
    public function shouldCompile($file)
    {
        if (!$this->cacheCompiled)
            return true;


        $path = $this->getCompiledPath($file);

        if (!file_exists($path) || filemtime($path) < filemtime($file)) {
            return true;
        }

        return false;
    }

    /**
     * Get the view path for a given view
     */
    public function getCompilerFromPath(string $path)
    {
        foreach ($this->extensions as $extension => $compiler) {
            if (str_ends_with($path, '.' . $extension)) {
                return $compiler;
            }
        }

        throw new CompilerException('the given path does not have  in not found: ' . $path);
    }

    public function registerCompiler(
        string $extension,
        string $compilerName,
        \Closure $resolver
    ) {
        $this->extensions[$extension] = $compilerName;
        $this->compilers[$compilerName] = $resolver;
    }

    public function resolveCompiler(
        string $compilerName
    ) {
        if (!isset($this->compilers[$compilerName])) {
            throw new CompilerException('the given path extension does not have a compiler registered: ' . $compilerName);
        }

        $compiler = $this->compilers[$compilerName]($this);

        return $this->resolvedCompilers[$compilerName] = $compiler;
    }

    /**
     * Set the compiled view path
     */
    public function setCompiledPath(string $compiledPath)
    {
        if (!file_exists($compiledPath)) {
            mkdir($compiledPath, 0744);
        }

        $this->compiledPath = $compiledPath;

        return $this;
    }

    /**
     * Set the compiled view file extension
     */
    public function setCompiledExtension(string $ext)
    {
        $this->compiledExtension = '.' . $ext;

        return $this;
    }

    /**
     * If the compiler should chache the compiled view file
     */
    public function cacheCompiled(bool $cache)
    {
        $this->cacheCompiled = $cache;

        return $this;
    }

    public function directive(string $key, $callback)
    {
        if (!isset($this->directives[$key]))
            $this->directives[$key] = $callback;
    }

    public function getDirectives()
    {
        return $this->directives;
    }

    public function getPrecompilers(): array
    {
        return $this->precompilers;
    }
}
