<?php

namespace app\core\view\Compilers;

use app\Core\Application;
use app\core\view\Compilers\ComponentCompiler;

class CompilerFactory
{

    protected Application $app;

    /**
     * The path to the aplication views directory
     */
    protected string $path;

    /**
     * The path to the aplication components views directory
     */
    protected string $componentsPath;

    /**
     * The path to storage the components views directory
     */
    protected string $compiledPath;

    /**
     * If the application should compile every time that the application returns the view 
     * if should cache the compiled views
     */
    protected bool $cacheCompiled = false;

    /**
     * The extension of the compiled views
     */
    protected string $compiledExtension = 'php';


    protected array $extensions = [
        '.vue.phtml',
        '.phtml'
    ];

    protected array $directives = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function compile(string $path, array $data = [], $isComponent = false)
    {

        if ($isComponent && str_ends_with($this->viewPath($path), '.vue.phtml')) {
            return (new VueCompiler($this))->make($path, $data);
        }

        if ($isComponent) {
            return (new ComponentCompiler($this))->make($path, $data);
        }

        return (new ViewCompiler($this))->make($path, $data);
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
        return sha1('compiled ' . $file);
    }

    /**
     * If the given view should be compiled
     */
    public function shouldCompile($file)
    {
        if (!$this->cacheCompiled)
            return true;

        $path = $this->getCompiledPath($file);

        if (!file_exists($path) || filemtime($path) < filemtime($this->viewPath($file))) {
            return true;
        }

        return false;
    }

    /**
     * Get the view path for a given view
     */
    public function viewPath($name)
    {
        $path = $this->path . $name;

        if (file_exists($path . end($this->extensions))) {
            return $path . end($this->extensions);
        }

        foreach ($this->extensions as $extension) {
            if (file_exists($path . $extension)) {
                return $path . $extension;
            }
        }

        throw new \InvalidArgumentException('the view in not found: ' . $path);
    }

    /**
     * Set the view path
     */
    public function setViewPath(string $path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0744);
        }

        $this->path = $path;

        return $this;
    }

    /**
     * Set the component path
     */
    public function setComponentPath(string $componentPath)
    {
        if (!file_exists($componentPath)) {
            mkdir($componentPath, 0744);
        }

        $this->componentsPath = $componentPath;

        return $this;
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

}
?>